<?php

namespace franchise\controllers;

use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Custom user controller
 * 
 * @inheritdoc
 */
class UserController extends \amnah\yii2\user\controllers\DefaultController {

    public $layout = "@franchise/views/layouts/main-login";

    /**
     * @inheritdoc
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'actions' => ['logout'],
                'allow' => true,
                'roles' => ['?', '@'],
            ],
            [
                'actions' => ['login', 'forgot', 'reset'],
                'allow' => true,
                'roles' => ['?'],
            ],
            [
                'actions' => ['confirm', 'resend', 'index', 'profile', 'account', 'cancel', 'resend-change', 'login-email', 'login-callback', 'register'],
                'allow' => false,
            ],
        ];

        $behaviors['access']['denyCallback'] = function($rule, $action) {
            $this->redirect(['/site/index']);
        };
        return $behaviors;
    }

    /**
     * Display login page
     */
    public function actionLogin() {
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
            $confirmError = Yii::t('app', 'franchise.controllers.account_not_activated', ['ru'=>"Учетная запись не активирована!"]);
            if (isset($test['email'][0]) && ($test['email'][0] !== $confirmError)) {
                $model->clearErrors();
                $model->addError('password', Yii::t('app', 'franchise.controllers.wrong_email', ['ru'=>'Вы указали неверную почту или пароль']));
            }
        }

        return $this->render('login', compact("model"));
    }
}
