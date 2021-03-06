<?php

namespace frontend\modules\clientintegr\modules\tillypad\controllers;

use api\common\models\iiko\search\iikoDicSearch;
use api\common\models\tillypad\TillypadService;
use Yii;

class DefaultController extends \frontend\modules\clientintegr\modules\iiko\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;

    public function actionIndex()
    {
        $searchModel = new iikoDicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $license = TillypadService::getLicense(Yii::$app->user->identity->organization_id);
        $view = $license ? 'index' : '/default/_nolic';
        $params = ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'lic' => $license];
        $spravoch_zagruzhen = iikoDicSearch::getDicsLoad();
        if ($spravoch_zagruzhen) {
            return Yii::$app->response->redirect(['clientintegr/tillypad/waybill/index']);
        } else {
            if (Yii::$app->request->isPjax) {
                return $this->renderPartial($view, $params);
            } else {
                return $this->render($view, $params);
            }
        }
    }

    public function actionMain()
    {
        $searchModel = new iikoDicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $license = TillypadService::getLicense(Yii::$app->user->identity->organization_id);
        $view = $license ? 'index' : '/default/_nolic';
        $params = ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'lic' => $license];
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($view, $params);
        } else {
            return $this->render($view, $params);
        }
    }
}
