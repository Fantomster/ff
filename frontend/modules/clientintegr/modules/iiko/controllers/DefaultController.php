<?php

namespace frontend\modules\clientintegr\modules\iiko\controllers;

use api\common\models\iiko\iikoWaybill;
use api\common\models\iiko\iikoWaybillData;
use api\common\models\iiko\search\iikoDicSearch;
use api\common\models\iiko\iikoService;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;
use Yii;
use yii\httpclient\Response;

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;

    public function actionIndex()
    {
        $searchModel = new iikoDicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $license = iikoService::getLicense();
        $view = $license ? 'index' : '/default/_nolic';
        $params = ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'lic' => $license];
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($view, $params);
        } else {
            return $this->render($view, $params);
        }
    }

    public function actionTest() {
        $model = iikoWaybill::findOne(7);
        header('Content-type: text/xml');
        echo $model->getXmlDocument();
    }
}
