<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercService;
use api\common\models\merc\MercVisits;
use api\common\models\merc\search\mercStockEntrySearch;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\getStockEntry;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\LoadStockEntryList;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\models\createStoreEntryForm;
use frontend\modules\clientintegr\modules\merc\models\dateForm;
use frontend\modules\clientintegr\modules\merc\models\expiryDate;
use frontend\modules\clientintegr\modules\merc\models\inputDate;
use frontend\modules\clientintegr\modules\merc\models\productionDate;
use Yii;

class StockEntryController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;

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
        $searchModel = new mercStockEntrySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $params = ['searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'lic' => $lic];

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', $params);
        } else {
            return $this->render('index', $params);
        }
    }

    public function actionNolic()
    {
        return $this->render('/default/_nolic');
    }

    public function actionView($uuid)
    {
        //try {
        $document = new getStockEntry();
        $document->loadStockEntry($uuid);
        /*}catch (\Error $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        }
        catch (\Exception $e){
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        }*/
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
                        if(!isset($result))
                            throw new \Exception('Error create Stock entry');

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

    private function updateStockEntryList()
    {
        $visit = MercVisits::getLastVisit(Yii::$app->user->identity->organization_id, MercVisits::LOAD_STOCK_ENTRY);
        $transaction = Yii::$app->db_api->beginTransaction();
        try {
            $vsd = new LoadStockEntryList();
            if (isset($visit))
                $visit = gmdate("Y-m-d H:i:s", strtotime($visit) - 60 * 30);
            $vsd->updateData($visit);
            MercVisits::updateLastVisit(Yii::$app->user->identity->organization_id, MercVisits::LOAD_STOCK_ENTRY);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
        }
    }

    public function actionProducersList($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $res = [];
            $list = cerberApi::getInstance()->getForeignEnterpriseList($q);
            if (isset($list->enterpriseList->enterprise)) {
                $res = [];
                foreach ($list->enterpriseList->enterprise as $item) {
                    if (($item->last) && ($item->active))
                        $res[] = ['id' => $item->guid,
                            'text' => $item->name . '(' .
                                $item->address->addressView
                                . ')'
                        ];
                }
            }
            $list = cerberApi::getInstance()->getRussianEnterpriseList($q);
            if (isset($list->enterpriseList->enterprise)) {

                foreach ($list->enterpriseList->enterprise as $item) {
                    if (($item->last) && ($item->active))
                        $res[] = ['id' => $item->guid,
                            'text' => $item->name . '(' .
                                $item->address->addressView
                                . ')'
                        ];
                }
            }
            if (count($res) > 0)
                $out['results'] = $res;

        }
        return $out;
    }

    private function getErrorText($e)
    {
        if ($e->getCode() == 600)
            return "При обращении к api Меркурий возникла ошибка. Ошибка зарегистрирована в журнале за номером №" . $e->getMessage() . ". Если ошибка повторяется обратитесь в техническую службу.";
        else
            return "При обращении к api Меркурий возникла ошибка. Если ошибка повторяется обратитесь в техническую службу.";
    }
}
