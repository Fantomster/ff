<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use common\models\User;
use Yii;
use api\common\models\RkAgent;
use api\common\models\RkAgentSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;

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

        if (Yii::$app->request->post("RkAgentSearch")) {
            $params['RkAgentSearch'] = Yii::$app->request->post("RkAgentSearch");
            if (isset($params['RkAgentSearch']['searchString'])) {
                $search_string = $params['RkAgentSearch']['searchString'];
            }
            if (isset($params['RkAgentSearch']['noComparison'])) {
                if ($params['RkAgentSearch']['noComparison'] != 0) {
                    $all_no_comparison = 1;
                    $searchModel->noComparison = 1;
                } else {
                    $all_no_comparison = 0;
                    $searchModel->noComparison = 0;
                }
            }
        } else {
            $search_string = null;
            $all_no_comparison = 0;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $search_string, $all_no_comparison);
        $searchModel->searchString = $search_string;

        return $this->render('view', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'noComparison' => $all_no_comparison
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
            $sql = "SELECT id, `name` as text FROM organization where `name` LIKE '%$term%' and type_id = 2 and id in (SELECT supp_org_id FROM relation_supp_rest where rest_org_id = $organisation_id and deleted = 0)";
        } else {
            $sql = "SELECT id, `name` as text FROM organization where type_id = 2 and id in (SELECT supp_org_id FROM relation_supp_rest where rest_org_id = $organisation_id and deleted = 0)";
        }
        $db = \Yii::$app->db;
        $data = $db->createCommand($sql)->queryAll();
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
        try {
            if (!$agent->save()) {
                throw new \Exception('Не удалось сохранить контрагента R-Keeper.');
            }
        } catch (\Exception $e) {
            \yii::error('Не удалось сохранить контрагента R-Keeper.');
            return false;
        }
        return true;
    }

}
