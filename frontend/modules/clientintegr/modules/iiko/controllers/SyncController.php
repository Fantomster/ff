<?php

namespace frontend\modules\clientintegr\modules\iiko\controllers;

use api\common\models\iiko\iikoAgent;
use api\common\models\iiko\iikoCategory;
use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoStore;
use api_web\modules\integration\modules\iiko\models\iikoSync as WebApiIikoSync;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use common\models\search\iikoAgentSearch;
use Yii;
use common\models\RelationSuppRest;
use common\models\Organization;

class SyncController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    public $organisation_id;
    public $ajaxActions = ['run'];

    public function beforeAction($action)
    {
        $user = User::findOne(\Yii::$app->user->id);
        $this->organisation_id = $user->organization_id;

        if (empty($this->organisation_id)) {
            return false;
        }

        if (in_array($this->action->id, $this->ajaxActions)) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            set_time_limit(3600);
        }

        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => $this->ajaxActions,
                'rules' => [
                    [
                        'allow'         => true,
                        'verbs'         => ['POST'],
                        'matchCallback' => function () {
                            return \Yii::$app->request->isAjax;
                        },
                    ],
                ],
            ]
        ];
    }

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'agent-mapping' => [                                       // identifier for your editable column action
                'class'           => EditableColumnAction::className(),     // action class name
                'modelClass'      => iikoAgent::className(),                // the model for the record being edited
                'outputValue'     => function ($model, $attribute, $key, $index) {
                    $vendor = $model->vendor;
                    return isset($vendor) ? $vendor->name : null;      // return any custom output value if desired
                },
                'outputMessage'   => function ($model, $attribute, $key, $index) {
                    return '';                                  // any custom error to return after model save
                },
                'showModelErrors' => true,                        // show model validation errors after save
                'errorOptions'    => ['header' => ''],              // error summary HTML options
                'postOnly'        => true,
                'ajaxOnly'        => true,
            ]
        ]);
    }

    /**
     * @return string
     */
    public function actionGoodsView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoProduct::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('goods-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionCategoryView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoCategory::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('category-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionStoreView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoStore::find()->where(['org_id' => $this->organisation_id])
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
        $searchModel = new iikoAgentSearch;
        $params = Yii::$app->request->getQueryParams();
        $organization = User::findOne(Yii::$app->user->id)->organization_id;
        $searchModel->load(Yii::$app->request->get());
        $dataProvider = $searchModel->search($params, $organization);
        return $this->render('agent-view', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * Синхронизация всего, по типам
     *
     * @return array
     */
    public function actionRun()
    {
        $id = \Yii::$app->request->post('id');
        try {
            return (new WebApiIikoSync())->run($id);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTraceAsString()];
        }
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
        $agent = iikoAgent::findOne($id);
        $agent->vendor_id = $vendor_id;
        return $agent->save();
    }

    /**
     * Редактирование комментария у агента
     *
     * @return boolean
     */
    public function actionEditComment()
    {
        $comment = Yii::$app->request->post('comm');
        $id = Yii::$app->request->post('number');
        $agent = iikoAgent::findOne($id);
        $agent->comment = $comment;
        return $agent->save();
    }

    /**
     * Редактирование статуса активности у агента
     *
     * @return boolean
     */
    public function actionEditActive()
    {
        $activ = Yii::$app->request->post('activ');
        $id = Yii::$app->request->post('number');
        $agent = iikoAgent::findOne($id);
        $agent->is_active = $activ;
        return $agent->save();
    }

    /**
     * Редактирование задержки платежа у агента
     *
     * @return boolean
     */
    public function actionEditPaymentDelay()
    {
        $delay = Yii::$app->request->post('delay');
        $id = Yii::$app->request->post('number');
        if (ctype_digit($delay) && ($delay < 366)) {
            $agent = iikoAgent::findOne($id);
            $agent->payment_delay = $delay;
            return $agent->save();
        } else {
            return false;
        }
    }
}
