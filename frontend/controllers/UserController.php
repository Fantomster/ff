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
use yii\web\HttpException;

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
        $behaviors['access']['rules'][] = [
            'actions' => ['accept-restaurants-invite'],
            'allow' => true,
            'roles' => ['?'],
        ];
        $behaviors['access']['rules'][] = [
            'actions' => ['ajax-invite-friend'],
            'allow' => true,
            'roles' => ['@'],
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
                $test = ActiveForm::validate($user, $profile, $organization);
                return ActiveForm::validate($user, $profile, $organization);
            }

            // validate for normal request
            if ($user->validate() && $profile->validate() && $organization->validate()) {

                // perform registration
                $role = $this->module->model("Role");
                $user->setRegisterAttributes($role::getManagerRole($organization->type_id))->save();
//                $profile->setUser($user->id)->save();
//                $organization->save();
//                $user->setOrganization($organization->id)->save();
                sleep(1);
                $this->afterRegister($user);

                // set flash
                // don't use $this->refresh() because user may automatically be logged in and get 403 forbidden
                $successText = Yii::t("user", "Successfully registered [ {displayName} ]", ["displayName" => $user->getDisplayName()]);
                $guestText = "";
                if (Yii::$app->user->isGuest) {
                    $guestText = Yii::t("user", " - Please check your email to confirm your account");
                }
                Yii::$app->session->setFlash("Register-success", $successText . $guestText);
            }
        }

        return $this->render("register", compact("user", "profile", "organization"));
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

}
