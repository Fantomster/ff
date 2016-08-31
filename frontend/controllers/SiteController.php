<?php

namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use common\models\User;
use common\models\Role;
use common\components\AccessRule;
use yii\helpers\Url;

/**
 * Site controller
 */
class SiteController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'only' => ['logout', 'signup', 'index', 'about'],
                'rules' => [
                    [
                        'actions' => ['signup', 'index'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['index', 'about'],
                        'allow' => false,
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                        ],
                        'denyCallback' => function($rule, $action) {
                            $this->redirect(Url::to(['/client/index']));
                        }
                    ],
                    [
                        'actions' => ['index', 'about'],
                        'allow' => false,
                        'roles' => [
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                        ],
                        'denyCallback' => function($rule, $action) {
                            $this->redirect(Url::to(['/vendor/index']));
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex() {
        return $this->render('index');
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout() {
        return $this->render('about');
    }

    /*
     *  Chat test
     */

    public function actionChatTest() {
        $user = User::findIdentity(Yii::$app->user->id);

        if (Yii::$app->request->post()) {

            $name = Yii::$app->request->post('name');
            $message = Yii::$app->request->post('message');

            return Yii::$app->redis->executeCommand('PUBLISH', [
                        'channel' => 'notification',
                        'message' => Json::encode(['name' => $name, 'message' => $message])
            ]);
        }


        return $this->render('chat-test', ['user' => $user, 'channel' => 'testchan']);
    }

}
