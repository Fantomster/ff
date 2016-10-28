<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\User;
use common\models\Profile;
use common\models\Organization;
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
                        'actions' => ['signup', 'index', 'about'],
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
        $user = new User();
        $user->scenario = 'register';
        $profile = new Profile();
        $profile->scenario = 'register';
        $organization = new Organization();
        $organization->scenario = 'register';
        return $this->render('index', compact("user", "profile", "organization"));
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout() {
        return $this->render('about');
    }
    
    public function actionContacts() {
        return $this->render('contacts');
    }
    public function actionFaq() {
        return $this->render('faq');
    }
    public function actionRestaurant() {
        $user = new User();
        $user->scenario = 'register';
        $profile = new Profile();
        $profile->scenario = 'register';
        $organization = new Organization();
        $organization->scenario = 'register';
        return $this->render('restaurant', compact("user", "profile", "organization"));
    }
    public function actionSupplier() {
        $user = new User();
        $user->scenario = 'register';
        $profile = new Profile();
        $profile->scenario = 'register';
        $organization = new Organization();
        $organization->scenario = 'register';
        return $this->render('supplier', compact("user", "profile", "organization"));
    }
}
