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
use yii\web\HttpException;
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
                'only' => ['logout', 'signup', 'index', 'about', 'complete-registration', 'ajax-tutorial-off', 'ajax-tutorial-on', 'faq', 'restaurant', 'supplier'],
                'rules' => [
                    [
                        'actions' => ['signup', 'index', 'about', 'faq', 'restaurant', 'supplier'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'complete-registration', 'ajax-tutorial-off', 'ajax-tutorial-on'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['index', 'about', 'faq', 'restaurant', 'supplier'],
                        'allow' => false,
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                        ],
                        'denyCallback' => function($rule, $action) {
                            $user = Yii::$app->user->identity;
                            if (empty($user->organization)) {
                                throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
                            }
            
                            //if ($this->isRegistrationComplete($user->organization)) {
                                $this->redirectOrganizationIndex($user->organization);
                            //} else {
                            //    $this->redirect(['/site/complete-registration']);
                            //}
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
//        $profile = $user->profile;
//        $profile->scenario = "complete";
        $organization = $user->organization;
        $organization->scenario = "complete";
        
        $post = Yii::$app->request->post();
        if ($organization->load($post)) {
            if ($organization->validate()) {
                
                //$profile->save();
                $organization->step = Organization::STEP_TUTORIAL;
                $organization->save();
                $user->sendWelcome();
                
                //Временный скрипт оповещания входа клиентов delivery-club
                if(strpos($user->email, '@delivery-club.ru')){
                    $text = "[ " . $organization->name . " ] [ " . $profile->phone . " ] вошел в систему f-keeper";
                    $target = '89296117900,89099056888';
                    $sms = new \common\components\QTSMS();
                    $sms->post_message($text, $target); 
                }
                return $this->redirect(['/site/index']);  
            }
        }
        
        //return $this->render("complete-registration", compact("profile", "organization"));
        return $this->render("complete-registration", compact("organization"));
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
    
    private function isRegistrationComplete($organization) {
        return ($organization->step != Organization::STEP_SET_INFO);
    }
    
    private function redirectOrganizationIndex($organization) {
        if ($organization->type_id === Organization::TYPE_RESTAURANT) {
            $this->redirect(['/client/index']);
        }
        if ($organization->type_id === Organization::TYPE_SUPPLIER) {
            $this->redirect(['/vendor/index']);
        }
    }
}
