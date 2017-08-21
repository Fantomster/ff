<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\controllers;

use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\Profile;
use common\models\Organization;
use yii\filters\AccessControl;

/**
 * Custom user controller
 * 
 * @inheritdoc
 */
class UserController extends \amnah\yii2\user\controllers\DefaultController {

    public $layout = "@frontend/views/layouts/main-auth";

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [[
                'actions' => ['confirm', 'resend', 'logout'],
                'allow' => true,
                'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['login', 'register', 'forgot', 'reset', 'login-email', 'login-callback', 'accept-restaurants-invite', 'ajax-register'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'profile', 'account', 'cancel', 'resend-change'],
                        'allow' => false,
                    ],
                    [
                        'actions' => ['ajax-invite-friend'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
                'denyCallback' => function($rule, $action) {
                    $this->redirect('\site\index');
                }                
            ]
        ];
    }

    public function actionAjaxRegister() {
        if (!Yii::$app->request->isAjax) {
            throw new \yii\web\HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = $this->module->model("User", ["scenario" => "register"]);
        $profile = $this->module->model("Profile", ["scenario" => "register"]);
        $organization = $this->module->model("Organization");

        $organization->step = Organization::STEP_SET_INFO;

        // load post data
        $post = Yii::$app->request->post();
        if ($user->load($post)) {

            // ensure profile data gets loaded
            $profile->load($post);

            // validate for existing email
            if (User::findOne(['email' => $user->email])) {
                return ['result' => 'fail', 'message' => 'Email занят'];
            }

            // validate for normal request
            if ($user->validate() && $profile->validate()) {

                // perform registration
                $role = $this->module->model("Role");

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $user->setRegisterAttributes($role::getManagerRole($organization->type_id))->save();
                    if ($profile->setUser($user->id)->save() && $organization->save() && $user->setOrganization($organization, true)->save()) {
                        $transaction->commit();
                        $this->afterRegister($user);
                        return ['result' => 'success', 'message' => 'Регистрация прошла успешно'];
                    } else {
                        $transaction->rollBack();
                        return ['result' => 'fail', 'message' => 'Неизвестная ошибка'];
                    }
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    return ['result' => 'fail', 'message' => 'Неизвестная ошибка'];
                }
            }
        }
    }

    /**
     * Display registration page
     */
    public function actionRegister() {
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
                    $transaction->commit();
                } catch (Exception $ex) {
                    $transaction->rollBack();
                }
                if ($organization->type_id == Organization::TYPE_SUPPLIER) {
                    //$this->initDemoData($user, $profile, $organization);
                }
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
    public function actionConfirm($token) {
        /** @var \amnah\yii2\user\models\UserToken $userToken */
        /** @var \amnah\yii2\user\models\User $user */
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
            $email = $newEmail ? : $user->email;
            $userToken->delete();
            $this->performLogin($user, true);
            return $this->redirect(['/site/index', 'new' => true]);
        }

        $invalidToken = true;
        return $this->render("reset", compact("invalidToken"));
    }

    /**
     * Accept restaurant's invite
     */
    public function actionAcceptRestaurantsInvite($token) {
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
    public function actionLogin() {
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
            return ActiveForm::validate($model);
        }

        if ($model->load($post) && $model->validate()) {
            $returnUrl = $this->performLogin($model->getUser(), $model->rememberMe);
            return $this->redirect($returnUrl);
        }

//        if ($model->hasErrors()) {
//            $test = $model->errors;
//            $confirmError = "Учетная запись не активирована!";
//            if (isset($test['email'][0]) && ($test['email'][0] !== $confirmError)) {
//                $model->clearErrors();
//                $model->addError('password', 'Вы указали неверную почту или пароль');
//            }
//        }
        
        
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
            Yii::$app->session->setFlash("Forgot-success", Yii::t("user", "Instructions to reset your password have been sent"));
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
            $user->save();
            $success = true;
            //\api\modules\v1\modules\mobile\components\NotificationHelper::actionForgot($user);
        }

        return $this->render('reset', compact("user", "success"));
    }

    public function actionAjaxInviteFriend() {
        $currentUser = Yii::$app->user->identity;
        if (Yii::$app->request->isAjax) {
            $email = Yii::$app->request->post('email');
            //$validator = new \yii\validators\EmailValidator();
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$email)  && $currentUser->sendInviteToFriend($email))
            {
               return [
                    'success' => true,
                ];
            }
        }
        return ['success' => false];
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

    private function initDemoData($user, $profile, $organization) {
        $transaction = Yii::$app->dbDemo->beginTransaction();
        try {
            Yii::$app->dbDemo->createCommand()->insert('organization', [
                'id' => $organization->id,
                'type_id' => $organization->type_id,
                'name' => $organization->name,
            ])->execute();
            Yii::$app->dbDemo->createCommand()->insert('user', [
                'id' => $user->id,
                'role_id' => $user->role_id,
                'status' => User::STATUS_ACTIVE,
                'email' => $user->email,
                'password' => $user->password,
                'auth_key' => $user->auth_key,
                'access_token' => $user->access_token,
                'created_ip' => $user->created_ip,
                'created_at' => $user->created_at,
                'organization_id' => $user->organization_id,
            ])->execute();
            Yii::$app->dbDemo->createCommand()->insert('profile', [
                'id' => $profile->id,
                'user_id' => $profile->user_id,
                'created_at' => $profile->created_at,
                'full_name' => $profile->full_name,
                'phone' => $profile->phone,
                'sms_allow' => $profile->sms_allow,
            ])->execute();
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollback();
            return false;
        }
    }

}
