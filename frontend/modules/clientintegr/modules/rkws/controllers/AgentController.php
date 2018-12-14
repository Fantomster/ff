<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use common\models\RelationSuppRest;
use common\models\User;
use Yii;
use api\common\models\RkAgent;
use api\common\models\RkAgentSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use common\models\Organization;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */
class AgentController extends \frontend\modules\clientintegr\controllers\DefaultController
{

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'agent-mapping' => [                                       // identifier for your editable column action
                'class'           => EditableColumnAction::className(),     // action class name
                'modelClass'      => RkAgent::className(),                // the model for the record being edited
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

    public function actionIndex()
    {

        $searchModel = new RkAgentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->render('index', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

    }

    public function actionView()
    {
        $searchModel = new \common\models\search\RkAgentSearch;
        $params = Yii::$app->request->getQueryParams();
        $organization = User::findOne(Yii::$app->user->id)->organization_id;
        $searchModel->load(Yii::$app->request->post());
        $dataProvider = $searchModel->search($params, $organization);
        return $this->render('view', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    public function actionGetws()
    {

        //  $resres = ApiHelper::getAgents();

        $res = new \frontend\modules\clientintegr\modules\rkws\components\AgentHelper();
        $res->getAgents();

        $this->redirect('/clientintegr/rkws/default');

    }

    protected function findModel($id)
    {
        if (($dmodel = \api\common\models\RkDic::findOne($id)) !== null) {

            $model = RkAgent::find()->andWhere('acc = :acc', [':acc' => $dmodel->org_id]);

            $dataProvider = new ActiveDataProvider([
                'query' => $model,
                'sort'  => false]);

            return $dataProvider;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
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
        $agent = RkAgent::findOne($id);
        $agent->vendor_id = $vendor_id;
        if (!$agent->save()) {
            return false;
        }
        return true;
    }

}
