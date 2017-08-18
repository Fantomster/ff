<?php

namespace backend\controllers;

use common\models\Franchisee;
use common\models\FranchiseeAssociate;
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
                        'actions' => ['index', 'view', ],
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
     * Displays a single Organization model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

//    /**
//     * Creates a new Organization model.
//     * If creation is successful, the browser will be redirected to the 'view' page.
//     * @return mixed
//     */
//    public function actionCreate()
//    {
//        $model = new Organization();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        } else {
//            return $this->render('create', [
//                'model' => $model,
//            ]);
//        }
//    }
//
    /**
     * Updates an existing Organization model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        $franchiseesObj = Franchisee::find()->all();
        $franchisees = ArrayHelper::map($franchiseesObj,'id','legal_entity');
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $post = Yii::$app->request->post();
            $franchiseeAssociate = FranchiseeAssociate::findOne(['organization_id'=>$id]);
            if(!$franchiseeAssociate){
                $franchiseeAssociate = new FranchiseeAssociate();
                $franchiseeAssociate->organization_id = $id;
            }
            $franchiseeAssociate->franchisee_id = $post['Franchisee']['id'];
            $franchiseeAssociate->save();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', compact('model', 'franchisees'));
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
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
