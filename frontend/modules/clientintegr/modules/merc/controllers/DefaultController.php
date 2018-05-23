<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\search\mercDicSearch;
use api\common\models\merc\mercService;
use frontend\modules\clientintegr\modules\merc\helpers\mercApi;
use frontend\modules\clientintegr\modules\merc\helpers\vetDocumentDonePartial;
use frontend\modules\clientintegr\modules\merc\helpers\vetDocumentsList;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentByUUIDRequest;
use frontend\modules\clientintegr\modules\merc\models\rejectedForm;
use Yii;

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;

    public function beforeAction($action)
    {
        $license = mercService::getLicense();
        if (!$license)
            return $this->render('/default/_nolic');
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function actionIndex()
    {
        $dataProvider = (new vetDocumentsList())->getArrayDataProvider();
        $license = mercService::getLicense();
        $params = [/*'searchModel' => $searchModel, */
            'dataProvider' => $dataProvider, 'lic' => $license];
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', $params);
        } else {
            return $this->render('index', $params);
        }
    }

    public function actionTest() {
        $api = mercApi::getInstance();
        $api->GetVetDocumentList();
    }

    public function actionView($uuid)
    {
        $document = new getVetDocumentByUUIDRequest();
        $document->getDocumentByUUID($uuid);
        $license = mercService::getLicense();
        $params = ['document' => $document, 'lic' => $license];
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('view', $params);
        } else {
            return $this->render('view', $params);
        }
    }

    public function actionDone($uuid)
    {
        $api = mercApi::getInstance();
        $api->getVetDocumentDone($uuid);

        $cache = \Yii::$app->cache;
        $cache->delete('vetDocRaw_'.$uuid);
        $cache->delete('vetDoc_'.$uuid);

        $document = new getVetDocumentByUUIDRequest();
        $document->getDocumentByUUID($uuid);

        $license = mercService::getLicense();
        $params = ['document' => $document, 'lic' => $license];
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('view', $params);
        } else {
            return $this->render('view', $params);
        }
    }

    public function actionDonePartial($uuid, $reject = false)
    {
        $model = new rejectedForm();
        if($reject)
            $model->decision = vetDocumentDonePartial::RETURN_ALL;
        else
            $model->decision = vetDocumentDonePartial::PARTIAL;

            if ($model->load(Yii::$app->request->post()) && $model->validate())
            {
                $api = mercApi::getInstance();
                $api->getVetDocumentDonePartial($uuid, $model->attributes);

                $cache = \Yii::$app->cache;
                $cache->delete('vetDocRaw_'.$uuid);
                $cache->delete('vetDoc_'.$uuid);

                if (Yii::$app->request->isAjax)
                    return true;
                return $this->redirect(['view', 'uuid' => $uuid]);
            }

        $document = new getVetDocumentByUUIDRequest();
        $document->getDocumentByUUID($uuid);

        //var_dump($document->batch[4]);

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
}
