<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\mercDicconst;
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

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController
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
            $this->updateVSDList();
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function actionIndex()
    {
        Yii::$app->cache->flush();
        $lic = mercService::getLicense();
        $searchModel = new mercVSDSearch();
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

            if (!$api->getVetDocumentDone($uuid))
                throw new \Exception('Done error');

        } catch (\Error $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }

        Yii::$app->session->setFlash('success', 'ВСД успешно погашен!');
        $this->updateVSDList();
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
        if ($reject)
            $model->decision = VetDocumentDone::RETURN_ALL;
        else
            $model->decision = VetDocumentDone::PARTIALLY;

        try {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $api = mercuryApi::getInstance();

                if (!$api->getVetDocumentDone($uuid, $model->attributes))
                    throw new \Exception('Done error');

                Yii::$app->session->setFlash('success', 'ВСД успешно погашен!');
                if (Yii::$app->request->isAjax)
                    return true;
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

        if (Yii::$app->request->isAjax)
            return $this->renderAjax('rejected/_ajaxForm', [
                'model' => $model,
                'volume' => $document->batch[4]['value']
            ]);

        return $this->render('rejected/rejectedAct', [
            'model' => $model,
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
                if (!$api->getVetDocumentDone($uuid))
                    throw new \Exception('Done error');
            }
        } catch (\Error $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        }

        if (!$error)
            Yii::$app->session->setFlash('success', 'ВСД успешно погашены!');
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

    public function actionGetPdf($uuid) {
        $vsdHttp = new \frontend\modules\clientintegr\modules\merc\components\VsdHttp([
            'authLink' => Yii::$app->params['vtsHttp']['authLink'],
            'vsdLink' => Yii::$app->params['vtsHttp']['vsdLink'],
            'pdfLink' => Yii::$app->params['vtsHttp']['pdfLink'],
            'username' => mercDicconst::getSetting("vetis_login"),
            'password' => mercDicconst::getSetting("vetis_password"), //'2wsx2WSX', //
        ]);
        $data = $vsdHttp->getPdfData($uuid);
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        header('Content-Disposition: attachment; filename=' . $uuid.'pdf');
        header("Content-type:application/pdf");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        flush();
        echo $data;
    }
    
    private function updateVSDList()
    {
        $hand_only = mercDicconst::getSetting('hand_load_only');
        if($hand_only == 1)
            return true;

        $visit = MercVisits::getLastVisit(Yii::$app->user->identity->organization_id, MercVisits::LOAD_VSD);
        $transaction = Yii::$app->db_api->beginTransaction();
       try {
            $vsd = new VetDocumentsChangeList();
            if(isset($visit))
                $visit = gmdate("Y-m-d H:i:s",strtotime($visit) - 60*30);
            $vsd->updateData($visit);
            MercVisits::updateLastVisit(Yii::$app->user->identity->organization_id, MercVisits::LOAD_VSD);
            $transaction->commit();
        }catch (\Exception $e)
        {
           $transaction->rollback();
            Yii::error($e->getMessage());
        }
    }

    private function getErrorText($e)
    {
        Yii::error($e->getMessage()." ".$e->getTraceAsString());
        if ($e->getCode() == 600)
            return "При обращении к api Меркурий возникла ошибка. Ошибка зарегистрирована в журнале за номером №".$e->getMessage().". Если ошибка повторяется обратитесь в техническую службу.";
        else
            return "При обращении к api Меркурий возникла ошибка. Если ошибка повторяется обратитесь в техническую службу.";
    }
}
