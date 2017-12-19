<?php

namespace backend\controllers;

use common\models\Organization;
use Yii;
use common\models\Franchisee;
use common\models\FranchiseeGeo;
use common\models\User;
use common\models\Profile;
use common\models\Role;
use common\models\FranchiseeUser;
use backend\models\FranchiseeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * FranchiseeController implements the CRUD actions for Franchisee model.
 */
class FranchiseeController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'delete-user' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['index', 'geo', 'geo-delete', 'update', 'view', 'create', 'delete', 'users', 'update-user', 'create-user', 'delete-user'],
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
        $searchModel = new FranchiseeSearch();
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
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Franchisee model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Franchisee();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Franchisee model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
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
     * Deletes an existing Franchisee model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionUsers($id) {
        $franchisee = $this->findModel($id);
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $franchisee->getUsers(),
            //'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('users', compact('franchisee', 'dataProvider'));
    }
    
    public function actionCreateUser($fr_id) {
        $user = new User(['scenario' => 'manage']);
        $user->password = uniqid();
        $profile = new Profile();

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {

                    $user->setRegisterAttributes($user->role_id, User::STATUS_ACTIVE)->save();
                    $profile->setUser($user->id)->save();
                    $user->setFranchisee($fr_id);
                    $model = new Organization();
                    $model->sendGenerationPasswordEmail($user, true);
                    return $this->redirect(['franchisee/users', 'id' => $fr_id]);
                }
            }
        }

        return $this->render('create-user', compact('user', 'profile', 'fr_id'));
    }
    public function actionGeo($id)
    {
        $franchisee = $this->findModel($id);
        $franchiseeGeoList = FranchiseeGeo::find()->where(['franchisee_id' => $id])->all();
        $franchiseeGeo = new FranchiseeGeo();
        $franchiseeGeo->franchisee_id = $id;
        if ($franchiseeGeo->load(Yii::$app->request->post()) && $franchiseeGeo->validate()) {
            $franchiseeGeo->save();
        }
        
        if (Yii::$app->request->isPjax) {
            return $this->renderAjax('geo', [
                'franchiseeGeoList' => $franchiseeGeoList,
                'franchisee' => $franchisee,
                'franchiseeGeo' => $franchiseeGeo,
            ]);
        }else{
            return $this->render('geo', [
                'franchiseeGeoList' => $franchiseeGeoList,
                'franchisee' => $franchisee,
                'franchiseeGeo' => $franchiseeGeo,
            ]);
        }
    }
    public function actionGeoDelete($id)
    {
     $franchiseeGeo = FranchiseeGeo::findOne($id);
     if($franchiseeGeo)
        {
            $franchiseeGeo->delete();
        }
    }
    /**
     * Finds the Franchisee model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Franchisee the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Franchisee::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('message', 'backend.controllers.franchisee.error', ['ru'=>'The requested page does not exist.']));
        }
    }
}
