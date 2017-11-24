<?php

namespace backend\controllers;

use Yii;
use common\models\Organization;
use common\models\DeliveryRegions;
use common\models\User;
use common\models\Profile;
use common\models\Role;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * FranchiseeController implements the CRUD actions for Franchisee model.
 */
class DeliveryRegionsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['index', 'regions', 'remove'],
                        'allow' => true,
                        'roles' => [Role::ROLE_ADMIN],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Franchisee models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new \backend\models\SupplierSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Displays a single Franchisee model.
     * @param integer $id
     * @return mixed
     */

    public function actionRegions($id)
    {
        $supplier = $this->findModel($id);
        $regionsList = DeliveryRegions::find()->where(['supplier_id' => $id])->all();
        $deliveryRegions = new DeliveryRegions();
        $deliveryRegions->supplier_id = $id;
        if ($deliveryRegions->load(Yii::$app->request->post()) && $deliveryRegions->validate()) {
            $deliveryRegions->save();
        }
        
        if (Yii::$app->request->isPjax) {
            return $this->renderAjax('regions', [
                'regionsList' => $regionsList,
                'supplier' => $supplier,
                'deliveryRegions' => $deliveryRegions,
            ]);
        }else{
            return $this->render('regions', [
                'regionsList' => $regionsList,
                'supplier' => $supplier,
                'deliveryRegions' => $deliveryRegions,
            ]);
        }
    }
    public function actionRemove($id)
    {
     $deliveryRegions = \common\models\DeliveryRegions::findOne($id);
     if($deliveryRegions)
        {
            $deliveryRegions->delete();
        }
    }
    /**
     * Finds the Franchisee model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Organization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Organization::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.delivery.error', ['ru'=>'The requested page does not exist.']));
        }
    }
}
