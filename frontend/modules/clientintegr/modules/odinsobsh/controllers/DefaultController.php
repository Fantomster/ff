<?php

namespace frontend\modules\clientintegr\modules\odinsobsh\controllers;

use api\common\models\one_s\OneSWaybill;
use api\common\models\one_s\OneSGood;
use api\common\models\one_s\OneSService;
use api\common\models\one_s\OneSStore;
use api\common\models\one_s\search\OneSDicSearch;
use Yii;
use yii\data\ActiveDataProvider;
use api\common\models\one_s\OneSContragent;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use common\models\Organization;
use common\models\RelationSuppRest;

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;
    public $organisation_id;

    public function beforeAction($action)
    {
        $this->organisation_id = User::findOne(Yii::$app->user->id)->organization_id;

        if(empty($this->organisation_id)) {
            return false;
        }

        return parent::beforeAction($action);
    }

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'agent-mapping' => [                                       // identifier for your editable column action
                'class' => EditableColumnAction::className(),     // action class name
                'modelClass' => OneSContragent::className(),                // the model for the record being edited
                'outputValue' => function ($model, $attribute, $key, $index) {
                    $vendor = $model->vendor;
                    return isset($vendor) ? $vendor->name : null;      // return any custom output value if desired
                },
                'outputMessage' => function($model, $attribute, $key, $index) {
                    return '';                                  // any custom error to return after model save
                },
                'showModelErrors' => true,                        // show model validation errors after save
                'errorOptions' => ['header' => '']  ,              // error summary HTML options
                'postOnly' => true,
                'ajaxOnly' => true,
            ]
        ]);
    }


    public function actionIndex()
    {
        $searchModel = new OneSDicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $license = OneSService::getLicense();
        $view = $license ? 'index' : '/default/_nolic';
        $params = ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'lic' => $license];
        $spravoch_zagruzhen = OneSDicSearch::getDicsLoad();
        if ($spravoch_zagruzhen) {
            return Yii::$app->response->redirect(['clientintegr/odinsobsh/waybill/index']);
        } else {
            if (Yii::$app->request->isPjax) {
                return $this->renderPartial($view, $params);
            } else {
                return $this->render($view, $params);
            }
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
        $searchModel = new \common\models\search\OneSAgentSearch;
        $params = Yii::$app->request->getQueryParams();
        $organization = User::findOne(Yii::$app->user->id)->organization_id;
        $searchModel->load(Yii::$app->request->get());
        $dataProvider = $searchModel->search($params, $organization);
        return $this->render('agent-view', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    public function actionMain()
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

    public function actionTest() {
        $model = oneSWaybill::findOne(7);
        header('Content-type: text/xml');
        echo $model->getXmlDocument();
    }

    /**
     * Формирование списка поставщиков по введённым символам
     *
     * @return array
     */
    public function actionAgentAutocomplete()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $term = Yii::$app->request->post('stroka');
        $user = User::findOne(\Yii::$app->user->id);
        $organisation_id = $user->organization_id;
        $out['results'] = [];

        if (!is_null($term)) {
            $vendors = RelationSuppRest::find()->select('supp_org_id')->where(['rest_org_id' => $organisation_id, 'deleted' => 0])->column();
            $data = Organization::find()->select('id,name')->
            where(['type_id' => 2])->
            andWhere(['in', 'id', $vendors])->
            andWhere(['like', 'name', ':term', [':term' => $term]])->
            orderBy(['name' => SORT_ASC])->all();
        } else {
            $vendors = RelationSuppRest::find()->select('supp_org_id')->where(['rest_org_id' => $organisation_id, 'deleted' => 0])->column();
            $data = Organization::find()->select('id,name')->
            where(['type_id' => 2])->
            andWhere(['in', 'id', $vendors])->
            orderBy(['name' => SORT_ASC])->all();
        }
        $out['results'] = array_values($data);

        return $out;
    }

    /**
     * Редактирование идентификатора поставщика у агента
     *
     * @return boolean
     */
    public function actionEditVendor()
    {
        $vendor_id = Yii::$app->request->post('id');
        $id = Yii::$app->request->post('number');
        $agent = OneSContragent::findOne($id);
        $agent->vendor_id = $vendor_id;
        return $agent->save();
    }
}
