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

/**
 * Custom user controller
 * 
 * @inheritdoc
 */
class UserController extends \amnah\yii2\user\controllers\DefaultController {

    public $layout = "@frontend/views/layouts/main-user";

    /**
     * @inheritdoc
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'actions' => ['confirm', 'resend', 'logout'],
                'allow' => true,
                'roles' => ['?', '@'],
            ],
            [
                'actions' => ['login', 'register', 'forgot', 'reset', 'login-email', 'login-callback', 'accept-restaurants-invite'],
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
        ];

        $behaviors['access']['denyCallback'] = function($rule, $action) {
            $this->redirect(['/site/index']);
        };
        return $behaviors;
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
                    $this->initDemoData($user, $profile, $organization);
                }
                $this->afterRegister($user);

                // set flash
                // don't use $this->refresh() because user may automatically be logged in and get 403 forbidden
                $successText = Yii::t("user", "Successfully registered [ {displayName} ]", ["displayName" => $user->getDisplayName()]);
                $guestText = "";
                if (Yii::$app->user->isGuest) {
                    $guestText = Yii::t("user", " - Please check your email to confirm your account");
                }
                Yii::$app->session->setFlash("Register-success", $successText . $guestText);
            } else {
                $profile->validate();
                $organization->validate();
            }
        }

        return $this->render("register", compact("user", "profile", "organization"));
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
            }
            if ($userToken->type == $userToken::TYPE_EMAIL_ACTIVATE) {
                //send welcome
                $user->sendWelcome();
            }
            // set email and delete token
            $email = $newEmail ? : $user->email;
            $userToken->delete();
        }

        return $this->render("confirm", compact("userToken", "success", "email"));
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
            $success = true;
            return $this->redirect(['/user/login']);
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

        // load post data and login
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $returnUrl = $this->performLogin($model->getUser(), $model->rememberMe);
            return $this->redirect($returnUrl);
        }

        if ($model->hasErrors()) {
            $test = $model->errors;
            $confirmError = "Учетная запись не активирована!";
            if (isset($test['email'][0]) && ($test['email'][0] !== $confirmError)) {
//                $model->clearErrors();
//                $model->addError('password', "Аккаунт не подтвержден. $confirmError");
//            } else {
                $model->clearErrors();
                $model->addError('password', 'Вы указали неверную почту или пароль');
            }
        }

        return $this->render('login', compact("model"));
    }

    public function actionAjaxInviteFriend() {
        $currentUser = Yii::$app->user->identity;
        if (Yii::$app->request->isAjax) {
            $email = Yii::$app->request->post('email');
            $validator = new \yii\validators\EmailValidator();
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if ($validator->validate($email) && $currentUser->sendInviteToFriend($email)) {
                return [
                    'success' => true,
                    'growl' => [
                        'options' => [
//                            'title' => 'test',
                        ],
                        'settings' => [
                            'element' => 'body',
                            'type' => 'Приглашение выслано!',
                            'allow_dismiss' => true,
                            'placement' => [
                                'from' => 'top',
                                'align' => 'center',
                            ],
                            'delay' => 1500,
                            'animate' => [
                                'enter' => 'animated fadeInDown',
                                'exit' => 'animated fadeOutUp',
                            ],
                            'offset' => 75,
                            'template' => '<div data-notify="container" class="modal-dialog" style="width: 340px;">'
                            . '<div class="modal-content">'
                            . '<div class="modal-header">'
                            . '<h4 class="modal-title">{0}</h4></div>'
                            . '<div class="modal-body form-inline" style="text-align: center; font-size: 36px;"> '
                            . '<span class="glyphicon glyphicon-thumbs-up"></span>'
                            . '</div></div></div>',
                        ]
                    ]
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
     * @return boolean
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
