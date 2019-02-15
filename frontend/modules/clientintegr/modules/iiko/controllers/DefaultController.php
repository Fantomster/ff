<?php

namespace frontend\modules\clientintegr\modules\iiko\controllers;

use api\common\models\iiko\iikoWaybill;
use api\common\models\iiko\search\iikoDicSearch;
use api\common\models\iiko\iikoService;
use Yii;

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;

    public function actionIndex()
    {
        $searchModel = new iikoDicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $license = iikoService::getLicense(Yii::$app->user->identity->organization_id);
        $view = $license ? 'index' : '/default/_nolic';
        $params = ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'lic' => $license];
        $spravoch_zagruzhen = iikoDicSearch::getDicsLoad();
        if ($spravoch_zagruzhen) {
            return Yii::$app->response->redirect(['clientintegr/iiko/waybill/index']);
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
        $license = iikoService::getLicense(Yii::$app->user->identity->organization_id);
        $view = $license ? 'index' : '/default/_nolic';
        $params = ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'lic' => $license];
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($view, $params);
        } else {
            return $this->render($view, $params);
        }
    }

    public function actionTest()
    {
        $model = iikoWaybill::findOne(7);
        header('Content-type: text/xml');
        echo $model->getXmlDocument();
    }
}
