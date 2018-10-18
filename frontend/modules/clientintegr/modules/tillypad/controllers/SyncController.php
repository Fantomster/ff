<?php

namespace frontend\modules\clientintegr\modules\tillypad\controllers;

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

class SyncController extends \frontend\modules\clientintegr\modules\iiko\controllers\SyncController
{
    public $enableCsrfValidation = false;
    public $organisation_id;
    public $ajaxActions = ['run'];

    /*public function beforeAction($action)
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
    }*/

    /*public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => $this->ajaxActions,
                'rules' => [
                    [
                        'allow' => true,
                        'verbs' => ['POST'],
                        'matchCallback' => function () {
                            return \Yii::$app->request->isAjax;
                        },
                    ],
                ],
            ]
        ];
    }*/

    /*public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'agent-mapping' => [                                       // identifier for your editable column action
                'class' => EditableColumnAction::className(),     // action class name
                'modelClass' => iikoAgent::className(),                // the model for the record being edited
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
    }*/

    /*
     * @return string
     */
    /*public function actionGoodsView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoProduct::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('goods-view', [
            'dataProvider' => $dataProvider,
        ]);
    }*/

    /*
     * @return string
     */
    /*public function actionCategoryView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoCategory::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('category-view', [
            'dataProvider' => $dataProvider,
        ]);
    }*/

    /*
     * @return string
     */
    /*public function actionStoreView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoStore::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('store-view', [
            'dataProvider' => $dataProvider,
        ]);
    }*/

    /*
     * @return string
     */
    /*public function actionAgentView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoAgent::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('agent-view', [
            'dataProvider' => $dataProvider,
        ]);
    }*/

    /*
     * Синхронизация всего, по типам
     * @return array
     */
    /*public function actionRun()
    {
        $id = \Yii::$app->request->post('id');
        try {
            return (new WebApiIikoSync())->run($id);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTraceAsString()];
        }
    }*/

    /*
     * Формирование списка поставщиков по введённым символам
     * @param null $term
     * @return mixed
     * @throws \yii\db\Exception
     */
    /*public function actionAgentAutocomplete($term = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!is_null($term)) {

            $sql = "SELECT id, `name` as text FROM organization where `name` LIKE '%$term%' and type_id = 2 and id in (SELECT supp_org_id FROM relation_supp_rest where rest_org_id = $this->organisation_id and deleted = 0)";
            $db = \Yii::$app->db;
            $data = $db->createCommand($sql)->queryAll();
            $out['results'] = array_values($data);
        }

        return $out;
    }*/
}
