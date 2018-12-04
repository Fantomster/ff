<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercPconst;
use api\common\models\merc\mercService;
use api\common\models\merc\MercVisits;
use api\common\models\merc\MercVsd;
use api\common\models\merc\search\mercVSDSearch;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentDone;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentsChangeList;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\getVetDocumentByUUID;
use frontend\modules\clientintegr\modules\merc\models\rejectedForm;
use Yii;
use common\components\AccessRule;
use yii\base\Exception;
use yii\filters\AccessControl;
use common\models\Role;

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController
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

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }
    
    public function actionIndex()
    {
        Yii::$app->cache->flush();
        $lic = mercService::getLicense();
        $searchModel = new mercVSDSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $user = Yii::$app->getUser()->identity;
        $params = ['searchModel'  => $searchModel,
                   'dataProvider' => $dataProvider,
                   'lic'          => $lic,
                   'user'         => $user
        ];

        if(!isset(Yii::$app->request->queryParams['page'])) {
            $this->updateVSDList();
        }

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
        try {
            
            $document = new getVetDocumentByUUID();
            $document->getDocumentByUUID($uuid);
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
    
    public function actionDone($uuid)
    {
        $start = Yii::$app->params['merc_settings']['start_date'];
        
        if ((MercVsd::find()->where("uuid = '$uuid' and date_doc >= '$start'")->one()) == null) {
            Yii::$app->session->setFlash('error', 'Для гашения сертификатов ВСД созданных до ' . Yii::$app->formatter->asDatetime($start, "php:j M Y") . ' необходимо перейти в систему Меркурий');
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        
        try {
            $api = mercuryApi::getInstance();
            
            if (!$api->getVetDocumentDone($uuid)) {
                throw new \Exception('Done error');
            }
        } catch (\Error $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        
        Yii::$app->session->setFlash('success', 'ВСД успешно погашен!');
        if (Yii::$app->request->isAjax) {
            return true;
        } else {
            return $this->redirect(['view', 'uuid' => $uuid]);
        }
    }
    
    public function actionDonePartial($uuid, $reject = false)
    {
        $start = Yii::$app->params['merc_settings']['start_date'];
        
        if ((MercVsd::find()->where("uuid = '$uuid' and date_doc >= '$start'")->one()) == null) {
            Yii::$app->session->setFlash('error', 'Для гашения сертификатов ВСД созданных до ' . Yii::$app->formatter->asDatetime($start, "php:j M Y") . ' необходимо перейти в систему Меркурий');
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        
        $model = new rejectedForm();
        if ($reject) {
            $model->decision = VetDocumentDone::RETURN_ALL;
        } else {
            $model->decision = VetDocumentDone::PARTIALLY;
        }
        
        try {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $api = mercuryApi::getInstance();

                if($model->mode == rejectedForm::INPUT_MODE) {
                    $vsd = MercVsd::findOne(['uuid' => $uuid]);
                    $conditions = $api->getRegionalizationConditions($vsd->recipient_guid, $vsd->sender_guid, $vsd->sub_product_guid);

                    if (isset($conditions)) {
                        $model->conditionsDescription = json_encode($conditions);
                        $model->mode = rejectedForm::CONFIRM_MODE;
                        if (Yii::$app->request->isAjax) {
                            return $this->renderAjax('rejected/_ajaxForm', [
                                'model'  => $model,
                                'volume' => $model->volume,
                            ]);
                        }

                        return $this->render('rejected/rejectedAct', [
                            'model'  => $model,
                            'volume' => $model->volume,
                        ]);
                    }
                }

                if (!$api->getVetDocumentDone($uuid, $model->attributes)) {
                    throw new \Exception('Done error');
                }
                
                Yii::$app->session->setFlash('success', 'ВСД успешно погашен!');
                if (Yii::$app->request->isAjax) {
                    return true;
                }
                return $this->redirect(['view', 'uuid' => $uuid]);
            }
        } catch (\Error $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        
        try {
            $document = new getVetDocumentByUUID();
            $document->getDocumentByUUID($uuid);
        } catch (\Error $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('rejected/_ajaxForm', [
                'model'  => $model,
                'volume' => $document->batch[4]['value']
            ]);
        }
        
        return $this->render('rejected/rejectedAct', [
            'model'  => $model,
            'volume' => $document->batch[4]['value']
        ]);
    }
    
    public function actionDoneAll()
    {
        $selected = Yii::$app->request->get('selected');
        $start = Yii::$app->params['merc_settings']['start_date'];
        $error = false;
        
        try {
            $selected = explode(',', $selected);
            $api = mercuryApi::getInstance();
            foreach ($selected as $id) {
                $uuid = MercVsd::findOne(['id' => $id])->uuid;
                
                if ((MercVsd::find()->where("uuid = '$uuid' and date_doc >= '$start'")->one()) == null) {
                    Yii::$app->session->setFlash('error', 'Для гашения сертификатов ВСД созданных до ' . Yii::$app->formatter->asDatetime($start, "php:j M Y") . ' необходимо перейти в систему Меркурий');
                    $error = true;
                }
                if (!$api->getVetDocumentDone($uuid)) {
                    throw new \Exception('Done error');
                }
            }
        } catch (\Error $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        }
        
        if (!$error) {
            Yii::$app->session->setFlash('success', 'ВСД успешно погашены!');
        }
        $this->updateVSDList();
        return $this->redirect(['index']);
    }
    
    public function actionAjaxLoadVsd()
    {
        if (Yii::$app->request->post()) {
            $list = Yii::$app->request->post('list');
            
            $vsd = new VetDocumentsChangeList();
            
            if ($vsd->handUpdateData($list)) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ["title" => 'ВСД успешно загружены', "type" => "success"];
            }
        }
        return false;
    }
    
    public function actionAjaxCheckVetisPass()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $user = Yii::$app->user->identity;
        $mercPConst = mercPconst::find()->leftJoin('merc_dicconst', 'merc_dicconst.id=merc_pconst.const_id')->where('merc_pconst.org=:org', ['org' => $user->organization_id])->andWhere('merc_dicconst.denom="vetis_password"')->one();
        $mercPConstLogin = mercPconst::find()->leftJoin('merc_dicconst', 'merc_dicconst.id=merc_pconst.const_id')->where('merc_pconst.org=:org', ['org' => $user->organization_id])->andWhere('merc_dicconst.denom="vetis_login"')->one();
        if ($mercPConst && $mercPConst->value != '') {
            return ['success' => true, 'login' => $mercPConstLogin->value];
        }
        
        return ['success' => false, 'login' => $mercPConstLogin->value];
    }
    
    public function actionAjaxUpdateVetisAccessData()
    {
        $user = Yii::$app->user->identity;
        $pass = Yii::$app->request->get('pass');
        $mercPConst = mercPconst::find()->leftJoin('merc_dicconst', 'merc_dicconst.id=merc_pconst.const_id')->where('merc_pconst.org=:org', ['org' => $user->organization_id])->andWhere('merc_dicconst.denom="vetis_password"')->one();
        if ($mercPConst) {
            $mercPConst->value = $pass;
            $mercPConst->save();
        }
        
        $login = Yii::$app->request->get('login');
        $mercPConstLogin = mercPconst::find()->leftJoin('merc_dicconst', 'merc_dicconst.id=merc_pconst.const_id')->where('merc_pconst.org=:org', ['org' => $user->organization_id])->andWhere('merc_dicconst.denom="vetis_login"')->one();
        if ($mercPConstLogin) {
            $mercPConstLogin->value = $login;
            $mercPConstLogin->save();
        }
    }
    
    private function generateVsdHttp()
    {
        return new \frontend\modules\clientintegr\modules\merc\components\VsdHttp([
            'authLink'       => Yii::$app->params['vtsHttp']['authLink'],
            'vsdLink'        => Yii::$app->params['vtsHttp']['vsdLink'],
            'pdfLink'        => Yii::$app->params['vtsHttp']['pdfLink'],
            'shortPdfLink'        => Yii::$app->params['vtsHttp']['shortPdfLink'],
            'chooseFirmLink' => Yii::$app->params['vtsHttp']['chooseFirmLink'],
            'username'       => mercDicconst::getSetting("vetis_login"),
            'password'       => mercDicconst::getSetting("vetis_password"), //'2wsx2WSX', //
            'firmGuid'       => mercDicconst::getSetting("issuer_id"),
        ]);
    }
    
    public function actionGetPdf($uuid, $full)
    {
        $vsdHttp = $this->generateVsdHttp();
        $data = $vsdHttp->getPdfData($uuid, $full);
        \Yii::$app->response->headers->add('Content-Disposition','attachment; filename=' . $uuid . '.pdf');
        \Yii::$app->response->headers->add("Content-type", "application/pdf");
        \Yii::$app->response->headers->add('Expires', '0');
        \Yii::$app->response->headers->add('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        \Yii::$app->response->headers->add('Cache-Control', 'public');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        \Yii::$app->response->data = $data;
    }
    
    public function actionCheckAuthData()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $vsdHttp = $this->generateVsdHttp();
        $data = $vsdHttp->checkAuthData();
        
        return $data;
    }
    
    private function updateVSDList()
    {
        $hand_only = mercDicconst::getSetting('hand_load_only');
        if ($hand_only == 1) {
            return true;
        }

        MercVsd::getUpdateData(Yii::$app->user->identity->organization_id);
        return true; // in case of error return true anyway, like hand_only is set
    }
    
    private function getErrorText($e)
    {
        Yii::error($e->getMessage() . " " . $e->getTraceAsString());
        if ($e->getCode() == 600) {
            return "При обращении к api Меркурий возникла ошибка. Ошибка зарегистрирована в журнале за номером №" . $e->getMessage() . ". Если ошибка повторяется обратитесь в техническую службу.";
        } else {
            Yii::error($e->getMessage()." ".$e->getTraceAsString());
            return "При обращении к api Меркурий возникла ошибка. Если ошибка повторяется обратитесь в техническую службу.";
        }
    }
    
}
