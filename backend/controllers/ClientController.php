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
use common\models\Job;

/**
 * UserController implements the CRUD actions for User model.
 */
class ClientController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'update',
                            'managers',
                            'postavs',
                            'restors',
                            'delete',
                            'employees',
                        ],
                        'allow'   => true,
                        'roles'   => [
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
    public function actionIndex()
    {
        $searchModel    = new UserSearch();
        $dataProvider   = $searchModel->search(Yii::$app->request->queryParams);
        $exceptionArray = Role::getExceptionArray();
        Yii::$app->session->set("clients", 'index');
        Yii::$app->session->set("clients_name", 'Пользователи');
        return $this->render('index', compact('searchModel', 'dataProvider', 'exceptionArray'));
    }

    /**
     * Lists all employees
     */
    public function actionEmployees($id)
    {
        $organization_id = $id;
        $organization    = \common\models\Organization::findOne(['id' => $organization_id]);
        if (empty($organization)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'frontend.controllers.vendor.get_out', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        /** @var \common\models\search\UserSearch $searchModel */
        $searchModel                             = new \common\models\search\UserSearch();
        $params['UserSearch']                    = Yii::$app->request->post("UserSearch");
        $params['UserSearch']['organization_id'] = $organization_id;
        $dataProvider                            = $searchModel->search($params);

        return $this->render('employees', compact('searchModel', 'dataProvider', 'organization'));
    }

    /**
     * Lists all f-keeper managers.
     * @return mixed
     */
    public function actionManagers()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, Role::ROLE_FKEEPER_MANAGER);
        Yii::$app->session->set("clients", 'managers');
        Yii::$app->session->set("clients_name", 'Менеджеры MixCart');
        return $this->render('managers', [
                    'searchModel'  => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Возвращает список всех сотрудников поставщиков.
     * @return mixed
     */
    public function actionPostavs()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, [Role::ROLE_SUPPLIER_MANAGER, Role::ROLE_SUPPLIER_EMPLOYEE]);
        Yii::$app->session->set("clients", 'postavs');
        Yii::$app->session->set("clients_name", 'Сотрудники поставщиков');

        return $this->render('postavs', [
                    'searchModel'  => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Возвращает список всех сотрудников ресторанов.
     * @return mixed
     */
    public function actionRestors()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, [Role::ROLE_RESTAURANT_MANAGER, Role::ROLE_RESTAURANT_EMPLOYEE, Role::ROLE_ONE_S_INTEGRATION]);
        Yii::$app->session->set("clients", 'restors');
        Yii::$app->session->set("clients_name", 'Сотрудники ресторанов');
        return $this->render('restors', [
                    'searchModel'  => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $post = Yii::$app->request->post();

        $newPassModel = new ForgotForm();
        if ($newPassModel->load($post)) {
            Yii::$app->session->set('new_pass_session', 'true');
            $newPassModel->sendForgotEmail();
            // set flash (which will show on the current page)
            Yii::$app->session->setFlash("Forgot-success", Yii::t('message', 'backend.controllers.client.sent', ['ru' => 'Письмо отправлено пользователю']));
        }

        return $this->render('view', [
                    'model'        => $this->findModel($id),
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
    public function actionUpdate($id)
    {
        $user        = User::findOne(['id' => $id]);
        $profile     = Profile::findOne(['user_id' => $id]);
        $currentUser = User::findOne(Yii::$app->user->identity->id);

        if (in_array($user->role_id, Role::getExceptionArray())) {
            throw new HttpException(403, Yii::t('error', 'backend.controllers.client.moon', ['ru' => 'Редактирование этого аккаунта отключено во имя Луны!']));
        }

        if (empty($user)) {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.client.get_out', ['ru' => 'Нет здесь ничего такого, проходите, гражданин!']));
        }

        if (($user->id === 2) && (Yii::$app->user->identity->id !== 76)) {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.client.moon_two', ['ru' => 'Редактирование этого аккаунта отключено во имя Луны!']));
        }

        try {
            if ($user->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post()) && $user->validate(['organization_id', 'role_id', 'status']) && $profile->validate()) {
                if (($user->organization_id == 1) && (Yii::$app->user->identity->id !== 76)) {
                    throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.client.moon_three', ['ru' => 'Добавление пользователей в эту организацию отключено во имя Луны!']));
                }

                $user->save();
                //$profile->email = $user->getEmail();
                $profile->save();
                if ($user->role_id != Role::ROLE_FKEEPER_MANAGER && isset($user->organization_id)) {
                    $user->updateRelationUserOrganization($user->organization_id, $user->role_id);
                }
                return $this->redirect(['client/view', 'id' => $user->id]);
            } else {
                if (isset($user->organization_id) && $user->getRelationUserOrganizationRoleID($user->organization_id) && ($user->role_id != Role::ROLE_FKEEPER_MANAGER)) {
                    $dropDown = Role::dropdown(Role::getRelationOrganizationType($id, $user->organization_id));
                    $selected = $user->getRelationUserOrganizationRoleID($id);
                } else {
                    $dropDown[$user->role_id] = Role::getRoleName($user->role_id);
                    $selected                 = $user->role_id;
                }
                return $this->render('update', compact('user', 'profile', 'dropDown', 'selected', 'currentUser'));
            }
        } catch (Exception $e) {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.client.this_is_it', ['ru' => 'Ошибочка вышла!']));
        }
    }

    /**
     * Deactivates an existing manager.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = User::findOne(['id' => $id/* , 'role_id' => Role::ROLE_FKEEPER_MANAGER */]);

        if (empty($model)) {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.client.get_out_two', ['ru' => 'Нет здесь ничего такого, проходите, гражданин!']));
        }

        $role                   = $model->role_id;
        $model->role_id         = Role::ROLE_USER;
        $model->organization_id = null;
        $model->status          = User::STATUS_INACTIVE;
        $model->save();

        switch ($role) {
            case Role::ROLE_RESTAURANT_MANAGER:
                return $this->redirect(['restors']);
                break;
            case Role::ROLE_RESTAURANT_EMPLOYEE:
                return $this->redirect(['restors']);
                break;
            case Role::ROLE_ONE_S_INTEGRATION:
                return $this->redirect(['restors']);
                break;
            case Role::ROLE_SUPPLIER_MANAGER:
                return $this->redirect(['postavs']);
                break;
            case Role::ROLE_SUPPLIER_EMPLOYEE:
                return $this->redirect(['postavs']);
                break;
            default:
                return $this->redirect(['managers']);
        }
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.client.this_is_it_two', ['ru' => 'The requested page does not exist.']));
        }
    }

}
