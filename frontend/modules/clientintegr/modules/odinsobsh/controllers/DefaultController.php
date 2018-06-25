<?php

namespace frontend\modules\clientintegr\modules\odinsobsh\controllers;

use api\common\models\iiko\iikoWaybill;
use api\common\models\iiko\iikoWaybillData;
use api\common\models\iiko\search\iikoDicSearch;
use api\common\models\iiko\iikoService;
use api\common\models\one_s\OneSContragent;
use api\common\models\one_s\OneSGood;
use api\common\models\one_s\OneSService;
use api\common\models\one_s\OneSStore;
use api\common\models\one_s\search\OneSDicSearch;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;
use Yii;
use yii\httpclient\Response;
use yii\data\ActiveDataProvider;

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;

    public function actionIndex()
    {
        $searchModel = new OneSDicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $license = OneSService::getLicense();
        $view = $license ? 'index' : '/default/_nolic';
        $params = ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'lic' => $license];
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($view, $params);
        } else {
            return $this->render($view, $params);
        }
    }

    /**
     * @return string
     */
    public function actionGoodsView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => OneSGood::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('goods-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionStoreView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => OneSStore::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('store-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionAgentView()
    {
       $dataProvider = new ActiveDataProvider([
            'query' => OneSContragent::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('agent-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionTest() {
        $model = iikoWaybill::findOne(7);
        header('Content-type: text/xml');
        echo $model->getXmlDocument();
    }
}
