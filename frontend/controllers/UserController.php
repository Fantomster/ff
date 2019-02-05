<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\controllers;

use common\models\OrganizationSearch;
use common\models\RelationSuppRest;
use common\models\RelationUserOrganization;
use common\models\search\BusinessSearch;
use common\models\TestVendors;
use api_web\classes\UserWebApi;
use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\Role;
use common\models\Organization;
use yii\filters\AccessControl;

/**
 * Custom user controller
 *
 * @inheritdoc
 */
class UserController extends \amnah\yii2\user\controllers\DefaultController
{

    public $layout = "@frontend/views/layouts/main-auth";

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class'        => AccessControl::className(),
                'rules'        => [[
                    'actions' => ['confirm', 'resend', 'logout'],
                    'allow'   => true,
                    'roles'   => ['?', '@'],
                ],
                    [
                        'actions' => ['login', 'register', 'forgot', 'reset', 'login-email', 'login-callback', 'accept-restaurants-invite', 'ajax-register'],
                        'allow'   => true,
                        'roles'   => ['?'],
                    ],
                    [
                        'actions' => ['index', 'profile', 'account', 'cancel', 'resend-change'],
                        'allow'   => false,
                    ],
                    [
                        'actions' => ['ajax-invite-friend', 'business', 'change-form', 'change', 'create', 'delete-business'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => ['confirm-additional-email',],
                        'allow'   => true,
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    $this->redirect(['/site/index']);
                }
            ]
        ];
    }

    public function actionAjaxRegister()
    {
        if (!Yii::$app->request->isAjax) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'frontend.controllers.user.get_out', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = $this->module->model("User", ["scenario" => "register"]);
        $profile = $this->module->model("Profile", ["scenario" => "register"]);
        $organization = $this->module->model("Organization");

        $organization->step = Organization::STEP_TUTORIAL;

        // load post data
        $post = Yii::$app->request->post();
        if ($user->load($post)) {

            // ensure profile data gets loaded
            $profile->load($post);

            // validate for existing email
            if (User::findOne(['email' => $user->email])) {
                return ['result' => 'fail', 'message' => Yii::t('message', 'frontend.controllers.user.email_busy', ['ru' => 'Email занят'])];
            }

            // validate for normal request
            if ($user->validate() && $profile->validate()) {

                // perform registration
                $role = $this->module->model("Role");

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $user->setRegisterAttributes($role::getManagerRole($organization->type_id))->save();
                    $profile->email = $user->getEmail();
                    if ($profile->setUser($user->id)->save() && $organization->save() && $user->setOrganization($organization, true)->save()) {
                        $transaction->commit();
                        $this->afterRegister($user);
                        return ['result' => 'success', 'message' => Yii::t('message', 'frontend.controllers.user.success', ['ru' => 'Регистрация прошла успешно'])];
                    } else {
                        $transaction->rollBack();
                        return ['result' => 'fail', 'message' => Yii::t('error', 'frontend.controllers.user.error', ['ru' => 'Неизвестная ошибка'])];
                    }
                } catch (\Exception $ex) {
                    $transaction->rollBack();
                    return ['result' => 'fail', 'message' => Yii::t('error', 'frontend.controllers.user.error_two', ['ru' => 'Неизвестная ошибка'])];
                }
            }
        }
    }

    /**
     * Display registration page
     */
    public function actionRegister()
    {
        /** @var \common\models\User $user */
        /** @var \common\models\Profile $profile */
        /** @var \amnah\yii2\user\models\Role $role */
        /** @var \common\models\Organization $organization */
        // set up new user/profile/organization objects
        $user = $this->module->model("User", ["scenario" => "register"]);
        $profile = $this->module->model("Profile", ["scenario" => "register"]);
        $organization = $this->module->model("Organization", ["scenario" => "register"]);

        // load post data
        $post = Yii::$app->request->post();
        if ($user->load($post)) {

            // ensure profile data gets loaded
            $profile->load($post);

            // load organization data
            $organization->load($post);

            // validate for ajax request
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($user, $profile, $organization);
            }

            // validate for normal request
            if ($user->validate() && $profile->validate() && $organization->validate()) {

                // perform registration
                $role = $this->module->model("Role");

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $user->setRegisterAttributes($role::getManagerRole($organization->type_id))->save();
                    $profile->setUser($user->id)->save();
                    $organization->save();
                    $user->setOrganization($organization, true)->save();
                    $user->setRelationUserOrganization($organization->id, $role::getManagerRole($organization->type_id));
                    $transaction->commit();
                } catch (Exception $ex) {
                    $transaction->rollBack();
                }
                if ($organization->type_id == Organization::TYPE_SUPPLIER) {
                    //$this->initDemoData($user, $profile, $organization);
                }

                if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                    TestVendors::setGuides($organization);
                }

                Yii::$app->mailer->htmlLayout = '@common/mail/layouts/mail';
                $this->afterRegister($user);

                return $this->render("registerSuccess", compact("user"));
            } else {
                $profile->validate();
                $organization->validate();
            }
        }

        $model = $this->module->model("LoginForm");
        $registerFirst = true;
        return $this->render("login", compact("model", "user", "profile", "organization", "registerFirst"));
    }

    /**
     * Confirm email
     */
    public function actionConfirm($token)
    {
        /** @var \amnah\yii2\user\models\UserToken $userToken */
        /** @var common\models\User $user */
        // search for userToken
        $success = false;
        $email = "";
        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::findByToken($token, [$userToken::TYPE_EMAIL_ACTIVATE, $userToken::TYPE_EMAIL_CHANGE]);
        if ($userToken) {

            // find user and ensure that another user doesn't have that email
            //   for example, user registered another account before confirming change of email
            $user = $this->module->model("User");
            $user = $user::findOne($userToken->user_id);
            $user->setNotifications();
            $newEmail = $userToken->data;
            if ($user->confirm($newEmail)) {
                $success = true;
                Yii::$app->user->login($user, 1);
            }
            if ($userToken->type == $userToken::TYPE_EMAIL_ACTIVATE) {
                //send welcome
                //$user->sendWelcome();
            }
            // set email and delete token
            $email = $newEmail ?: $user->email;
            $userToken->delete();
            $this->performLogin($user, true);
            return $this->redirect(['/site/index', 'new' => true]);
        }

        $invalidToken = true;
        return $this->render("reset", compact("invalidToken"));
    }

    public function actionConfirmAdditionalEmail($token)
    {
        $additionalEmail = \common\models\AdditionalEmail::findOne(['token' => $token]);
        if (empty($additionalEmail)) {
            $success = false;
        } else {
            $additionalEmail->order_created = 1;
            $additionalEmail->order_canceled = 1;
            $additionalEmail->order_changed = 1;
            $additionalEmail->order_processing = 1;
            $additionalEmail->order_done = 1;
            $additionalEmail->confirmed = 1;
            $additionalEmail->token = null;
            $additionalEmail->save();
            $success = true;
        }
        return $this->render('additional-email', compact("success"));
    }

    /**
     * Accept restaurant's invite
     */
    public function actionAcceptRestaurantsInvite($token)
    {
        /** @var \amnah\yii2\user\models\User $user */
        /** @var \amnah\yii2\user\models\UserToken $userToken */
        // get user token
        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::findByToken($token, $userToken::TYPE_EMAIL_ACTIVATE);
        if (!$userToken) {
            return $this->render('acceptRestaurantsInvite', ["invalidToken" => true]);
        }

        // get user and set "acceptInvite" scenario
        $success = false;
        $user = $this->module->model("User");
        $user = $user::findOne($userToken->user_id);
        $profile = $user->profile;
        $organization = $user->organization;
        $user->setScenario("acceptInvite");

        // load post data and set user password
        if ($user->load(Yii::$app->request->post()) && $user->validate() && $profile->validate() && $organization->validate()) {
            $user->status = $user::STATUS_ACTIVE;
            $user->save();
            $profile->email = $user->getEmail();
            $profile->save();
            $organization->save();
            // delete userToken and set success = true
            $userToken->delete();
            $user->sendWelcome();
            $success = true;
            Yii::$app->user->login($user, 1);
            return $this->redirect(['/site/index']);
        }

        return $this->render('acceptRestaurantsInvite', compact("user", "profile", "organization", "success"));
    }

    /**
     * Display login page
     */
    public function actionLogin()
    {
        //$this->layout = '@app/views/layouts/main-login';
        /** @var \amnah\yii2\user\models\forms\LoginForm $model */
        $model = $this->module->model("LoginForm");

        $user = $this->module->model("User", ["scenario" => "register"]);
        $profile = $this->module->model("Profile", ["scenario" => "register"]);
        $organization = $this->module->model("Organization", ["scenario" => "register"]);
        $organization->type_id = Organization::TYPE_RESTAURANT;

        // load post data and login
        $post = Yii::$app->request->post();

        //ajax
        if ($model->load($post) && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $test = ActiveForm::validate($model);
            return $test;
        }

        if ($model->load($post) && $model->validate()) {
            $user = $model->getUser();

            $rel = RelationUserOrganization::findAll(['user_id' => $user->id]);
            if (!empty($user->organization_id)) {
                if (count($rel) > 1 || (
                    (
                        $user->role_id == Role::ROLE_ADMIN ||
                        $user->role_id == Role::ROLE_FKEEPER_MANAGER))) {
                    $returnUrl = $this->performLogin($user, 1);
                    return $this->redirect(['business']);
                }
            }
            $returnUrl = $this->performLogin($model->getUser(), $model->rememberMe);
            return $this->redirect($returnUrl);
        }

        $registerFirst = false;
        return $this->render('login', compact("model", "user", "profile", "organization", "registerFirst"));
    }

    /**
     * Forgot password
     */
    public function actionForgot()
    {
        /** @var \amnah\yii2\user\models\forms\ForgotForm $model */
        // load post data and send email
        $model = $this->module->model("ForgotForm");
        $post = Yii::$app->request->post();

        //ajax
        if ($model->load($post) && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($post)) {
            $model->sendForgotEmail();
            // set flash (which will show on the current page)
            Yii::$app->session->setFlash("Forgot-success", Yii::t('app', "Instructions to reset your password have been sent"));
        }

        return $this->render("forgot", compact("model"));
    }

    /**
     * Reset password
     */
    public function actionReset($token)
    {
        /** @var \amnah\yii2\user\models\User $user */
        /** @var \amnah\yii2\user\models\UserToken $userToken */
        // get user token and check expiration
        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::findByToken($token, $userToken::TYPE_PASSWORD_RESET);
        if (!$userToken) {
            return $this->render('reset', ["invalidToken" => true]);
        }

        // get user and set "reset" scenario
        $success = false;
        /**
         * @var $user User
         */
        $user = $this->module->model("User");
        $user = $user::findOne($userToken->user_id);
        $user->setScenario("reset");

        $post = Yii::$app->request->post();

        // ajax
        if ($user->load($post) && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        // load post data and reset user password
        if ($user->load($post) && $user->save()) {

            // delete userToken and set success = true
            $userToken->delete();
            $user->status = \common\models\User::STATUS_ACTIVE;
            $user->organization->save();
            $user->save();
            $success = true;
            //\api\modules\v1\modules\mobile\components\NotificationHelper::actionForgot($user);
        }

        return $this->render('reset', compact("user", "success"));
    }

    public function actionAjaxInviteFriend()
    {
        $currentUser = Yii::$app->user->identity;
        if (Yii::$app->request->isAjax) {
            $email = Yii::$app->request->post('email');
            //$validator = new \yii\validators\EmailValidator();
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if (preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email) && $currentUser->sendInviteToFriend($email)) {
                return [
                    'success' => true,
                ];
            }
        }
        return ['success' => false];
    }

    public function actionChangeForm(): String
    {
        $user = User::findIdentity(Yii::$app->user->id);
        $organization = new Organization();
        $searchModel = new BusinessSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['GuideSearch'] = Yii::$app->request->get("searchString");
        $dataProvider = $searchModel->search($params, null);

        return $this->renderAjax('_changeForm', compact('user', 'dataProvider', 'organization', 'searchModel'));
    }

    public function actionBusiness(): String
    {
        $user = User::findIdentity(Yii::$app->user->id);
        $searchModel = new BusinessSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['GuideSearch'] = Yii::$app->request->get("searchString");
        $dataProvider = $searchModel->search($params, null);

        $loginRedirect = $this->module->loginRedirect;
        $returnUrl = Yii::$app->user->getReturnUrl($loginRedirect);
        return $this->render('business', compact('user', 'dataProvider', 'returnUrl', 'searchModel'));
    }

    public function actionDeleteBusiness($id)
    {
        $user = User::findIdentity(Yii::$app->user->id);
        $currentOrganization = $user->organization;
        $organizationToDelete = Organization::findOne(['id' => $id]);

        $relationUserOrg = RelationUserOrganization::findOne(['user_id' => $user->id, 'organization_id' => $id]);

        if (empty($relationUserOrg) || !(in_array($relationUserOrg->role_id, [
                Role::ROLE_ADMIN,
                Role::ROLE_FKEEPER_MANAGER,
                Role::ROLE_RESTAURANT_MANAGER,
                Role::ROLE_SUPPLIER_MANAGER
            ]))) {
            return false;
        }

        if ($currentOrganization->setPrimary() && $organizationToDelete->wipeBusiness()) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ["title" => Yii::t('app', 'frontend.controllers.user.business_deleted', ['ru' => "Бизнес успешно удален!"]), "type" => "success"];
        }
    }

    public function actionChange(int $id)
    {
        return (new UserWebApi())->setOrganization(['organization_id' => $id]);
    }

    public function actionCreate()
    {
        $user = User::findIdentity(Yii::$app->user->id);
        $org = $user->organization;

        $parent_id = !empty($org->parent_id) ? $org->parent_id : $org->id;

        $networks = (new Query())
            ->select(['id', 'name', 'type_id'])
            ->from(Organization::tableName())
            ->where(['IN', 'id', [$org->id, $parent_id]])
            ->orWhere(['IN', 'parent_id', [$org->id, $parent_id]])
            ->all();

        $roles = [
            Role::ROLE_ADMIN,
            Role::ROLE_RESTAURANT_MANAGER,
            Role::ROLE_SUPPLIER_MANAGER,
            Role::ROLE_FKEEPER_MANAGER,
            Role::ROLE_FRANCHISEE_OWNER,
            Role::ROLE_FRANCHISEE_OPERATOR,
        ];

        $org = new Organization();
        if (Yii::$app->request->isAjax && $org->load(Yii::$app->request->post()) && in_array($user->role_id, $roles)) {
            $org->parent_id = $parent_id;
            $org->save();

            if ($org->type_id == Organization::TYPE_RESTAURANT) {
                TestVendors::setGuides($org);
            }

            foreach ($networks as $network) {
                $relSuppRest = new RelationSuppRest();
                if ($network['type_id'] == Organization::TYPE_RESTAURANT && $org->type_id == Organization::TYPE_SUPPLIER) {
                    $relSuppRest->setAttributes([
                        "rest_org_id" => $network['id'],
                        "supp_org_id" => $org->id
                    ]);
                }
                if ($network['type_id'] == Organization::TYPE_SUPPLIER && $org->type_id == Organization::TYPE_RESTAURANT) {
                    $relSuppRest->setAttributes([
                        "rest_org_id" => $org->id,
                        "supp_org_id" => $network['id']
                    ]);
                }
                $relSuppRest->status = 1;
                $relSuppRest->invite = RelationSuppRest::INVITE_ON;
                $relSuppRest->save();
            }

            $roleID = ($org->type_id == Organization::TYPE_RESTAURANT) ? Role::ROLE_RESTAURANT_MANAGER : Role::ROLE_SUPPLIER_MANAGER;
            $user->createRelationUserOrganization($org->id, $roleID);
            $relations = RelationUserOrganization::findAll([
                'organization_id' => $org->id,
                'role_id'         => [
                    Role::ROLE_RESTAURANT_MANAGER,
                    Role::ROLE_SUPPLIER_MANAGER
                ]
            ]);

            foreach ($relations as $relation) {
                /** @var User $user */
                $user = $relation->user;
                $user->createRelationUserOrganization($org->id, $roleID);
            }
        }
    }

    /*
     * initDemoData
     * 
     * Fills data at demo server for new organization
     * 
     * @param User $user
     * @param Profile $profile
     * @param Organization $organization
     * 
     * @return bool
     */

    private function initDemoData($user, $profile, $organization)
    {
        $transaction = Yii::$app->dbDemo->beginTransaction();
        try {
            Yii::$app->dbDemo->createCommand()->insert('organization', [
                'id'      => $organization->id,
                'type_id' => $organization->type_id,
                'name'    => $organization->name,
            ])->execute();
            Yii::$app->dbDemo->createCommand()->insert('user', [
                'id'              => $user->id,
                'role_id'         => $user->role_id,
                'status'          => User::STATUS_ACTIVE,
                'email'           => $user->email,
                'password'        => $user->password,
                'auth_key'        => $user->auth_key,
                'access_token'    => $user->access_token,
                'created_ip'      => $user->created_ip,
                'created_at'      => $user->created_at,
                'organization_id' => $user->organization_id,
            ])->execute();
            Yii::$app->dbDemo->createCommand()->insert('profile', [
                'id'         => $profile->id,
                'user_id'    => $profile->user_id,
                'created_at' => $profile->created_at,
                'full_name'  => $profile->full_name,
                'phone'      => $profile->phone,
                'sms_allow'  => $profile->sms_allow,
            ])->execute();
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollback();
            return false;
        }
    }

}
