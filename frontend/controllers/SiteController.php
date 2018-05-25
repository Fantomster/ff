<?php

namespace frontend\controllers;

use api_web\components\Notice;
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
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'only' => ['logout', 'signup', 'index', 'about', 'complete-registration', 'ajax-tutorial-off', 'ajax-tutorial-on', 'faq', 'restaurant', 'supplier', 'unsubscribe'],
                'rules' => [
                    [
                        'actions' => ['signup', 'index', 'about', 'faq', 'restaurant', 'supplier', 'unsubscribe'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'complete-registration', 'ajax-tutorial-off', 'ajax-tutorial-on', 'ajax-complete-registration', 'ajax-wizard-off', 'unsubscribe'],
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
                            Role::getFranchiseeEditorRoles(),
                        ],
                        'denyCallback' => function ($rule, $action) {
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
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }


    public function actionUnsubscribe($token)
    {
        $user = User::findOne(['access_token' => $token]);
        if ($user) {
//            $user->subscribe = 0;
//            $user->save();
            Yii::$app->user->login($user, 3600);
            $this->redirect(['settings/notifications']);
        } else {
            throw new HttpException(404, 'Page not found');
        }
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
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
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionPayment()
    {
        throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        //return $this->render('payment');
    }

    public function actionContacts()
    {
        return $this->render('contacts');
    }

    public function actionFaq()
    {
        return $this->render('faq');
    }

    public function actionRestaurant()
    {
        $user = new User();
        $user->scenario = 'register';
        $profile = new Profile();
        $profile->scenario = 'register';
        $organization = new Organization();
        $organization->scenario = 'register';
        return $this->render('restaurant', compact("user", "profile", "organization"));
    }

    public function actionSupplier()
    {
        $user = new User();
        $user->scenario = 'register';
        $profile = new Profile();
        $profile->scenario = 'register';
        $organization = new Organization();
        $organization->scenario = 'register';
        return $this->render('supplier', compact("user", "profile", "organization"));
    }

    public function actionCompleteRegistration()
    {
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

                return $this->redirect(['/site/index']);
            }
        }

        //return $this->render("complete-registration", compact("profile", "organization"));
        return $this->render("complete-registration", compact("organization"));
    }

    public function actionAjaxCompleteRegistration()
    {
        $user = Yii::$app->user->identity;
        $profile = new Profile();
        $profile = $user->profile;
        $profile->scenario = "complete";
        $organization = $user->organization;
        $organization->scenario = "complete";

        $post = Yii::$app->request->post();
        if (Yii::$app->request->isAjax && empty($organization->locality) && $profile->load($post) && $organization->load($post)) {
            if ($profile->validate() && $organization->validate()) {
                $profile->save();
                $organization->save();
                $organization->refresh();
                $contact = [
                    'name' => $profile->full_name,
                    'company_name' => $organization->name,
                    'email' => $user->email,
                    'phone' => $profile->phone,
                    'city' => $organization->locality,
                ];
                $amoFields = \common\models\AmoFields::findOne(['amo_field' => 'register']);
                if ($amoFields) {
                    Yii::$app->amo->send($amoFields->pipeline_id, $amoFields->responsible_user_id, 'Регистрация', $contact);
                }
                $user->sendWelcome();
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return \yii\widgets\ActiveForm::validate($profile, $organization);
    }

    public function actionAjaxWizardOff()
    {
        $user = Yii::$app->user->identity;
        $organization = $user->organization;
        if (Yii::$app->request->isAjax) {
            $organization->step = Organization::STEP_OK;
            $organization->save();
            //$user->sendWelcome();
            $result = true;
            if ($organization->locality == 'Москва') {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                $result = ["result" => "moscow"];
            }
            return $result;
        }
        return false;
    }

    public function actionAjaxTutorialOff()
    {
        $user = Yii::$app->user->identity;
        if (isset($user->organization)) {
            $organization = $user->organization;
            $organization->step = Organization::STEP_OK;
            return $organization->save();
        }
        return false;
    }

    public function actionAjaxTutorialOn()
    {
        $user = Yii::$app->user->identity;
        if (isset($user->organization)) {
            $organization = $user->organization;
            $organization->step = Organization::STEP_TUTORIAL;
            return $organization->save();
        }
        return false;
    }

    private function isRegistrationComplete($organization)
    {
        return ($organization->step != Organization::STEP_SET_INFO);
    }

    private function redirectOrganizationIndex($organization)
    {
        if ($organization->type_id === Organization::TYPE_RESTAURANT) {
            $this->redirect(['/client/index']);
        }
        if ($organization->type_id === Organization::TYPE_SUPPLIER) {
            $this->redirect(['/vendor/index']);
        }
    }

}
