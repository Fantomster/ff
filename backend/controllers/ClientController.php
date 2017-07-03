<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\Role;
use backend\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * UserController implements the CRUD actions for User model.
 */
class ClientController extends Controller {

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
                        'actions' => ['index', 'view', 'update', 'managers', 'delete'],
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
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all f-keeper managers.
     * @return mixed
     */
    public function actionManagers() {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, Role::ROLE_FKEEPER_MANAGER);

        return $this->render('managers', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

//    /**
//     * Creates a new User model.
//     * If creation is successful, the browser will be redirected to the 'view' page.
//     * @return mixed
//     */
//    public function actionCreate()
//    {
//        $model = new User();
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
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = User::findOne(['id' => $id, 'role_id' => Role::ROLE_FKEEPER_MANAGER]);

        if (empty($model)) {
            throw new NotFoundHttpException('Нет здесь ничего такого, проходите, гражданин!');
        }
        
        if (($model->id === 2) && (Yii::$app->user->identity->id !== 76)) {
            throw new NotFoundHttpException('Редактирование этого аккаунта отключено во имя Луны!');
        }

        if (($model->organization_id === 1) && (Yii::$app->user->identity->id !== 76)) {
            throw new NotFoundHttpException('Добавление пользователей в эту организацию отключено во имя Луны!');
        }

        try {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                            'model' => $model,
                ]);
            }
        } catch (Exception $e) {
            throw new NotFoundHttpException('Ошибочка вышла!');
        }
    }

    /**
     * Deactivates an existing manager.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $model = User::findOne(['id' => $id, 'role_id' => Role::ROLE_FKEEPER_MANAGER]);

        if (empty($model)) {
            throw new NotFoundHttpException('Нет здесь ничего такого, проходите, гражданин!');
        }

        $model->role_id = Role::ROLE_USER;
        $model->organization_id = null;
        $model->status = User::STATUS_INACTIVE;
        $model->save();

        return $this->redirect(['managers']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
