<?php

namespace backend\controllers;

use backend\models\TestVendorsSearch;
use common\models\Franchisee;
use common\models\FranchiseeAssociate;
use common\models\guides\Guide;
use common\models\RelationSuppRest;
use common\models\TestVendors;
use Yii;
use common\models\Organization;
use common\models\Role;
use backend\models\OrganizationSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * OrganizationController implements the CRUD actions for Organization model.
 */
class OrganizationController extends Controller {

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
                        'actions' => ['index', 'view', 'test-vendors', 'create-test-vendor', 'update-test-vendor', 'start-test-vendors-updating'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN,
//                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Organization models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new OrganizationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Lists all TestVendors models.
     * @return mixed
     */
    public function actionTestVendors() {
        $searchModel = new TestVendorsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('test-vendors', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Organization model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new TestVendors model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateTestVendor()
    {
        $model = new TestVendors();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['test-vendors']);
        } else {
            return $this->render('create-test-vendor', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates TestVendors model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdateTestVendor($id)
    {
        $model = TestVendors::findOne(['id'=>$id]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['test-vendors']);
        } else {
            return $this->render('update-test-vendor', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Updates TestVendors.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionStartTestVendorsUpdating()
    {
        $clients = Organization::findAll(['type_id'=>Organization::TYPE_RESTAURANT]);
            foreach ($clients as $client){
                TestVendors::setGuides($client);
            }
        return $this->redirect(['test-vendors']);
    }



    /**
     * Updates an existing Organization model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        $franchiseeModel = $this->findFranchiseeAssociateModel($id);
        $franchiseeList = ArrayHelper::map(Franchisee::find()->all(),'id','legal_entity');
        if ($model->load(Yii::$app->request->post()) && $model->save() && $franchiseeModel->load(Yii::$app->request->post()) && $franchiseeModel->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', compact('model', 'franchiseeModel', 'franchiseeList'));
        }
    }


//    /**
//     * Deletes an existing Organization model.
//     * If deletion is successful, the browser will be redirected to the 'index' page.
//     * @param integer $id
//     * @return mixed
//     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//
//        return $this->redirect(['index']);
//    }

    /**
     * Finds the Organization model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Organization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Organization::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.organization_page_error', ['ru'=>'The requested page does not exist.']));
        }
    }


    /**
     * Finds the Organization model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Organization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findFranchiseeAssociateModel($id) {
        if (($model = FranchiseeAssociate::findOne(['organization_id'=>$id])) == null) {
            $model = new FranchiseeAssociate();
        }
        return $model;
    }

}
