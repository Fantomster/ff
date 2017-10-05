<?php

namespace backend\controllers;

use amnah\yii2\user\models\forms\ForgotForm;
use common\models\Profile;
use Yii;
use common\models\User;
use common\models\Role;
use backend\models\UserSearch;
use yii\web\Controller;
use yii\web\HttpException;
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
        $exceptionArray = Role::getExceptionArray();
        return $this->render('index', compact('searchModel', 'dataProvider', 'exceptionArray'));
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
        $post = Yii::$app->request->post();

        $newPassModel = new ForgotForm();
        if ($newPassModel->load($post)) {
            Yii::$app->session->set('new_pass_session', 'true');
            $newPassModel->sendForgotEmail();
            // set flash (which will show on the current page)
            Yii::$app->session->setFlash("Forgot-success", 'Письмо отправлено пользователю');
        }

        return $this->render('view', [
                    'model' => $this->findModel($id),
                    'newPassModel' => $newPassModel
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
        $user = User::findOne(['id' => $id]);
        $profile = Profile::findOne(['user_id' => $id]);

        if(in_array($user->role_id, Role::getExceptionArray())){
            throw new HttpException(403, 'Редактирование этого аккаунта отключено во имя Луны!');
        }

        if (empty($user)) {
            throw new NotFoundHttpException('Нет здесь ничего такого, проходите, гражданин!');
        }

        if (($user->id === 2) && (Yii::$app->user->identity->id !== 76)) {
            throw new NotFoundHttpException('Редактирование этого аккаунта отключено во имя Луны!');
        }

        try {
            if ($user->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
                if (($user->organization_id == 1) && (Yii::$app->user->identity->id !== 76)) {
                    throw new NotFoundHttpException('Добавление пользователей в эту организацию отключено во имя Луны!');
                }
                $user->save();
                $profile->save();
                return $this->redirect(['client/view', 'id' => $user->id]);
            } else {
                return $this->render('update', compact('user', 'profile'));
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
