<?php

namespace backend\controllers;

use Yii;
use common\models\BuisinessInfo;
use common\models\Organization;
use common\models\Role;
use backend\models\BuisinessInfoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * BuisinessInfoController implements the CRUD actions for BuisinessInfo model.
 */
class BuisinessInfoController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['update', 'delete', 'approve'],
                        'allow' => true,
                        'roles' => [Role::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN,
//                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all BuisinessInfo models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new BuisinessInfoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BuisinessInfo model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new BuisinessInfo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
//    public function actionCreate()
//    {
//        $model = new BuisinessInfo();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        } else {
//            return $this->render('create', [
//                'model' => $model,
//            ]);
//        }
//    }

    /**
     * Updates an existing BuisinessInfo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing BuisinessInfo model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionApprove($id) {
        if (($wl = BuisinessInfo::findOne(['organization_id' => $id])) !== null) {
            return $this->redirect(['organization/index']);
        } elseif (($org = Organization::findOne($id)) !== null) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $new = new BuisinessInfo();
                $new->organization_id = $org->id;
                $new->legal_entity = $org->legal_entity;
                $new->legal_email = $org->email;
                $new->phone = $org->phone;
                $new->save();
                $org->es_status = !Organization::ES_INACTIVE;
                $org->white_list = true;
                $org->save();
                $transaction->commit();
                return $this->redirect(['buisiness-info/update', 'id' => $new->id]);
            } catch (Exception $e) {
                $transaction->rollback();
            }
        }
        return $this->redirect(['organization/index']);
    }

    /**
     * Finds the BuisinessInfo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BuisinessInfo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = BuisinessInfo::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.buisiness_error', ['ru'=>'The requested page does not exist.']));
        }
    }

}
