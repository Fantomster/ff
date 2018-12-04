<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercService;
use api\common\models\merc\MercStockEntry;
use api\common\models\merc\MercVisits;
use api\common\models\merc\search\mercStockEntrySearch;
use console\modules\daemons\classes\MercStoreEntryList;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\getStockEntry;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\LoadStockEntryList;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\models\createStoreEntryForm;
use frontend\modules\clientintegr\modules\merc\models\dateForm;
use frontend\modules\clientintegr\modules\merc\models\expiryDate;
use frontend\modules\clientintegr\modules\merc\models\inputDate;
use frontend\modules\clientintegr\modules\merc\models\productionDate;
use frontend\modules\clientintegr\modules\merc\models\rejectedForm;
use Yii;
use common\components\AccessRule;
use yii\filters\AccessControl;
use common\models\Role;

class StockEntryController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class'      => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'allow' => false,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_BUYER,
                            Role::ROLE_RESTAURANT_JUNIOR_BUYER,
                            Role::ROLE_RESTAURANT_ORDER_INITIATOR,
                        ],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        $lic = mercService::getLicense();

        if (!isset($lic) && ($this->getRoute() != 'clientintegr/merc/default/nolic')) {
            $this->redirect(['nolic']);
            return false;
        }

        if (!mercDicconst::checkSettings()) {
            $this->redirect(['/clientintegr/merc/settings']);
            return false;
        }

        if ($action->actionMethod == 'actionIndex')
            $this->updateStockEntryList();
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function actionIndex()
    {
        Yii::$app->cache->flush();
        $lic = mercService::getLicense();
        $params = Yii::$app->request->getQueryParams();
        $searchModel = new mercStockEntrySearch();
        $session = Yii::$app->session;

        if (Yii::$app->request->post("mercStockEntrySearch")) {
            $params['mercStockEntrySearch'] = Yii::$app->request->post("mercStockEntrySearch");
            $session['mercStockEntrySearch'] = Yii::$app->request->post("mercStockEntrySearch");
        }

        $params['mercStockEntrySearch'] = $session['mercStockEntrySearch'];

        $dataProvider = $searchModel->search($params);

        $selected = $session->get('selectedentry', []);

        $params = ['searchModel'  => $searchModel,
                   'dataProvider' => $dataProvider,
                   'lic'          => $lic,
                   'selected'     => $selected];

        if (Yii::$app->request->isAjax || Yii::$app->request->isPjax) {
            return $this->renderAjax('index', $params);
        } else {
            return $this->render('index', $params);
        }
    }

    public function actionSaveSelectedEntry() // метод сохранения изменений выделения "флажками" товаров
    {
        $selected = Yii::$app->request->get('selected');
        $state = Yii::$app->request->get('state');

        $session = Yii::$app->session;

        $list = $session->get('selectedentry', []);

        $current = !empty($selected) ? explode(",", $selected) : [];

        foreach ($current as $item) {

            if ($state) {
                if (!in_array($item, $list))
                    $list[] = $item;
            } else {
                $key = array_search($item, $list);
                unset($list[$key]);
            }
        }

        $session->set('selectedentry', $list);
        return true;
    }

    public function actionNolic()
    {
        return $this->render('/default/_nolic');
    }

    public function actionView($uuid)
    {
        try {
            $document = new getStockEntry();
            $document->loadStockEntry($uuid);
        } catch (\Error $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        }
        $params = ['document' => $document];
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_ajaxView', $params);
        } else {
            return $this->render('view', $params);
        }
    }

    public function actionCreate()
    {
        $model = new createStoreEntryForm();
        $productionDate = new productionDate();
        $expiryDate = new expiryDate();
        $inputDate = new inputDate();
        if ($model->load(Yii::$app->request->post()) && $productionDate->load(Yii::$app->request->post()) && $expiryDate->load(Yii::$app->request->post()) && $inputDate->load(Yii::$app->request->post())) {
            //var_dump($productionDate->first_date, date('d.m.Y H:i'));
            if (!Yii::$app->request->isAjax) {
                // var_dump("setp 2");
                $res = $model->validate() && $productionDate->validate() && $expiryDate->validate() && $inputDate->validate();
                if ($res) {
                    $model->dateOfProduction = $productionDate;
                    $model->expiryDate = $expiryDate;
                    $model->vsd_issueDate = $inputDate;

                    try {
                        $result = mercuryApi::getInstance()->resolveDiscrepancyOperation($model);
                        if (!isset($result))
                            throw new \Exception('Error create Stock entry');

                        Yii::$app->session->setFlash('success', 'Позиция добавлена на склад!');
                        return $this->redirect(['index']);
                    } catch (\Error $e) {
                        Yii::$app->session->setFlash('error', $this->getErrorText($e));
                        return $this->redirect(['index']);
                    } catch (\Exception $e) {
                        Yii::$app->session->setFlash('error', $this->getErrorText($e));
                        return $this->redirect(['index']);
                    }
                }
            }
        }
        $params = ['model' => $model, 'productionDate' => $productionDate, 'expiryDate' => $expiryDate, 'inputDate' => $inputDate];
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('add-stock-enrty/_mainForm', $params);
        } else {
            return $this->render('add-stock-enrty/create', $params);
        }
    }

    public function actionInventory($id)
    {
        $model = new rejectedForm();
        $data = MercStockEntry::findOne(['id' => $id]);
        $volume = $data->amount . " " . $data->unit;
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                try {
                    $form = new createStoreEntryForm();
                    $form->attributes = $model->attributes;
                    $result = mercuryApi::getInstance()->resolveDiscrepancyOperation($form, createStoreEntryForm::INV_PRODUCT, [$data->raw_data]);
                    if (!isset($result))
                        throw new \Exception('Error create Stock entry');
                    Yii::$app->session->setFlash('success', 'Позиция изменена!');
                    return $this->redirect(['index']);
                } catch (\Error $e) {
                    Yii::$app->session->setFlash('error', $this->getErrorText($e));
                    return $this->redirect(['index']);
                } catch (\Exception $e) {
                    Yii::$app->session->setFlash('error', $this->getErrorText($e));
                    return $this->redirect(['index']);
                }
            }
        }
        $params = ['model' => $model, 'volume' => $volume];
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('inventory-stock-enrty/_ajaxForm', $params);
        } else {
            return $this->render('inventory-stock-enrty/create', $params);
        }
    }

    public function actionInventoryAll()
    {
        $selected = Yii::$app->session->get('selectedentry', []);
        try {
            $datas = [];
            foreach ($selected as $id) {
                $datas[] = (MercStockEntry::findOne(['id' => $id]))->raw_data;
            }

            $form = new createStoreEntryForm();
            $result = mercuryApi::getInstance()->resolveDiscrepancyOperation($form, createStoreEntryForm::INV_PRODUCT_ALL, $datas);
            if (!isset($result))
                throw new \Exception('Error create Stock entry');
            Yii::$app->session->setFlash('success', 'Позиции списаны!');
            return $this->redirect(['index']);
        } catch (\Error $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        }
    }

    private function updateStockEntryList()
    {
        MercStockEntry::getUpdateData((\Yii::$app->user->identity)->organization_id);
    }

    public function actionProducersList($q = null, $c = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        $res = [];
        if (!is_null($q)) {
            if ($c !== '74a3cbb1-56fa-94f3-ab3f-e8db4940d96b' && $c != null) {
                $res = [];
                $list = cerberApi::getInstance()->getForeignEnterpriseList($q, $c);
                if (isset($list)) {
                    $res = [];
                    foreach ($list as $item) {
                        if (($item->last) && ($item->active))
                            $res[] = ['id'   => $item->guid,
                                      'text' => $item->name . '(' .
                                          $item->address->addressView
                                          . ')'
                            ];
                    }
                }
            }

            if ($c == '74a3cbb1-56fa-94f3-ab3f-e8db4940d96b' || $c == null) {
                $list = cerberApi::getInstance()->getRussianEnterpriseList($q);
                if (isset($list)) {

                    foreach ($list as $item) {
                        if (($item->last) && ($item->active))
                            $res[] = ['id'   => $item->guid,
                                      'text' => $item->name . '(' .
                                          $item->address->addressView
                                          . ')'
                            ];
                    }
                }
            }
            if (count($res) > 0)
                $out['results'] = $res;

        }
        return $out;
    }

    private function getErrorText($e)
    {
        if ($e->getCode() == 600) {
            return "При обращении к api Меркурий возникла ошибка. Ошибка зарегистрирована в журнале за номером №" . $e->getMessage() . ". Если ошибка повторяется обратитесь в техническую службу.";
        }
        else {
            Yii::error($e->getMessage() . " " . $e->getTraceAsString());
            return "При обращении к api Меркурий возникла ошибка. Если ошибка повторяется обратитесь в техническую службу.";
        }
    }
}
