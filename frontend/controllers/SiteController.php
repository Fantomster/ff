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
                'only' => ['logout', 'signup', 'index', 'about', 'complete-registration', 'ajax-tutorial-off', 'ajax-tutorial-on'],
                'rules' => [
                    [
                        'actions' => ['signup', 'index', 'about'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'complete-registration', 'ajax-tutorial-off', 'ajax-tutorial-on'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['index', 'about'],
                        'allow' => false,
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                        ],
                        'denyCallback' => function($rule, $action) {
                            if ($this->isRegistrationComplete()) {
                                $this->redirect(['/client/index']);
                            } else {
                                $this->redirect(['/site/complete-registration']);
                            }
                        }
                    ],
                    [
                        'actions' => ['index', 'about'],
                        'allow' => false,
                        'roles' => [
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_ADMIN,
                        ],
                        'denyCallback' => function($rule, $action) {
                            if ($this->isRegistrationComplete()) {
                                $this->redirect(['/vendor/index']);
                            } else {
                                $this->redirect(['/site/complete-registration']);
                            }
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
        $sql = "select rest_count,supp_count from main_counter";
        $counter = \Yii::$app->db->createCommand($sql)->queryOne();
        return $this->render('index', compact("user", "profile", "organization", "counter"));
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
    
    public function actionCompleteRegistration() {
        $this->layout = "main-user";
        
        $user = Yii::$app->user->identity;
        $profile = $user->profile;
        $organization = $user->organization;
        
        $post = Yii::$app->request->post();
        if ($profile->load($post) && $organization->load($post)) {
            if ($profile->validate() && $organization->validate()) {
                $profile->save();
                $organization->step = Organization::STEP_TUTORIAL;
                $organization->save();
                $this->redirect(['/site/index']);
            }
        }
        
        return $this->render("complete-registration", compact("profile", "organization"));
    }
    
    public function actionAjaxTutorialOff() {
        $user = Yii::$app->user->identity;
        if (isset($user->organization)) {
            $organization = $user->organization;
            $organization->step = Organization::STEP_OK;
            return $organization->save();
        }
        return false;
    }
    
    public function actionAjaxTutorialOn() {
        $user = Yii::$app->user->identity;
        if (isset($user->organization)) {
            $organization = $user->organization;
            $organization->step = Organization::STEP_TUTORIAL;
            return $organization->save();
        }
        return false;
    }
    
    private function isRegistrationComplete() {
        $user = Yii::$app->user->identity;
        if (isset($user->organization)) {
            return ($user->organization->step != Organization::STEP_SET_INFO);
        }
        return false;
    }
}
