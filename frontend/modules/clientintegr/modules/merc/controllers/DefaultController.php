<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\search\mercDicSearch;
use api\common\models\merc\mercService;
use frontend\modules\clientintegr\modules\merc\helpers\mercApi;
use frontend\modules\clientintegr\modules\merc\helpers\vetDocumentsList;
use Yii;

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;

    public function actionSettings()
    {
        $searchModel = new mercDicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $license = mercService::getLicense();
        $view = $license ? 'index' : '/default/_nolic';
        $params = ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'lic' => $license];
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($view, $params);
        } else {
            return $this->render($view, $params);
        }
    }

    public function actionIndex()
    {
        //$searchModel = new mercDicSearch();
        $dataProvider = $dataProvider = (new vetDocumentsList())->getArrayDataProvider();
        $license = mercService::getLicense();
        $view = $license ? 'index' : '/default/_nolic';
        $params = [/*'searchModel' => $searchModel, */
            'dataProvider' => $dataProvider, 'lic' => $license];
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($view, $params);
        } else {
            return $this->render($view, $params);
        }
    }

    public function actionTest() {
        $api = mercApi::getInstance();
        $api->GetVetDocumentList();
    }
}
