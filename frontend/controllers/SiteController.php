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
use yii\web\Response;

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
                        'actions' => ['logout', 'complete-registration', 'ajax-tutorial-off', 'ajax-tutorial-on', 'ajax-complete-registration', 'ajax-wizard-off'],
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

                return $this->redirect(['/site/index']);
            }
        }

        //return $this->render("complete-registration", compact("profile", "organization"));
        return $this->render("complete-registration", compact("organization"));
    }

    public function actionAjaxCompleteRegistration() {
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
                if($organization->locality == 'Москва' || $organization->administrative_area_level_1 == 'Московская область'){
                   $this->SendToAmo($organization, $profile, $user);  
                }
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return \yii\widgets\ActiveForm::validate($profile, $organization);
    }
    
    
    /**
     * 
     * @param Organization $organizationModel
     * @param Profile $profileModel
     * @param User $userModel
     * @return boolean
     */
    private function SendToAmo($organizationModel, $profileModel, $userModel) {
            $response = null;
            $lead_name = $organizationModel->name;
            $company_name = $organizationModel->name;
            $responsible_user_id = 1427371;
            $lead_status_id = ($organizationModel->type_id === Organization::TYPE_RESTAURANT) ? 465729 : 463335;
            $comment = $organizationModel->formatted_address;
            $city = $organizationModel->country . ", " . $organizationModel->locality;
            $contact_name = $profileModel->full_name; //Название добавляемого контакта
            $contact_phone = $profileModel->phone; //Телефон контакта
            $contact_email = $userModel->email; //Емейл контакта
            $lead_partner = ''; //Тип партнерства
            //АВТОРИЗАЦИЯ
            $user = array(
                'USER_LOGIN' => 'artur@f-keeper.ru', #логин
                'USER_HASH' => '74ed35efba91ce97c029ceb8006b447b' #Хэш для доступа к API
            );
            $subdomain = 'fkeeper';
            #Формируем ссылку для запроса
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/auth.php?type=json';
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            #Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($user));
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../../franchise/cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../../franchise./cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
            curl_close($curl);  #Завершаем сеанс cURL
            $Response = json_decode($out, true);
            
            //ПОЛУЧАЕМ ДАННЫЕ АККАУНТА
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/accounts/current'; #$subdomain уже объявляли выше
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            #Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../../franchise/cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../../franchise/cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            $Response = json_decode($out, true);
            $account = $Response['response']['account'];
            //ПОЛУЧАЕМ СУЩЕСТВУЮЩИЕ ПОЛЯ
            $amoAllFields = $account['custom_fields']; //Все поля
            $amoConactsFields = $account['custom_fields']['contacts']; //Поля контактов
            //ФОРМИРУЕМ МАССИВ С ЗАПОЛНЕННЫМИ ПОЛЯМИ КОНТАКТА
            //Стандартные поля амо:
            $sFields = array_flip(array(
                'PHONE', //Телефон. Варианты: WORK, WORKDD, MOB, FAX, HOME, OTHER
                'EMAIL', //Email. Варианты: WORK, PRIV, OTHER
                    )
            );
            
            //// Проверка на уже существующий контакт
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/list?query=' . $contact_email;    
            
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            #Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../../franchise/cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../../franchise/cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if (!$this->CheckCurlResponse($code)) {
                return false;
            }
            
            
            
            
            //ДОБАВЛЯЕМ СДЕЛКУ
            $leads['request']['leads']['add'] = array(
                array(
                    'name' => $lead_name,
                    'pipeline_id' => $lead_status_id,
                    'responsible_user_id' => $responsible_user_id, //id ответственного по сделке
                    'custom_fields' => [
                        [
                            'id' => 85130, //парнерство
                            'values' => [
                                [
                                    'value' => $lead_partner
                                ]
                            ]
                        ],
                        [
                            'id' => 544713, //комментарий
                            'values' => [
                                [
                                    'value' => $comment
                                ]
                            ]
                        ]
                    ]
                )
            );
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/leads/set';
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            #Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../../franchise/cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../../franchise/cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            $Response = json_decode($out, true);

            if (is_array($Response['response']['leads']['add']))
                foreach ($Response['response']['leads']['add'] as $lead) {
                    $lead_id = $lead["id"]; //id новой сделки
                };
            //ДОБАВЛЯЕМ СДЕЛКУ - КОНЕЦ
            //ДОБАВЛЕНИЕ КОНТАКТА
            $contact = array(
                'name' => $contact_name,
                'linked_leads_id' => array($lead_id), //id сделки
                'responsible_user_id' => $responsible_user_id, //id ответственного
                'custom_fields' => array(
                    array(
                        'id' => 58054,
                        'values' => array(
                            array(
                                'value' => $contact_phone,
                                'enum' => 'MOB'
                            )
                        )
                    ),
                    array(
                        'id' => 58056,
                        'values' => array(
                            array(
                                'value' => $contact_email,
                                'enum' => 'WORK'
                            )
                        )
                    ),
                    array(
                        'id' => 105128,
                        'values' => array(
                            array(
                                'value' => $city,
                                'type_id' => 1,
                                'multiple' => 'N',
                            )
                        )
                    )
                )
            );
            $set['request']['contacts']['add'][] = $contact;
            #Формируем ссылку для запроса
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/set';
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            #Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($set));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../../franchise/cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../../franchise/cookie/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if (!$this->CheckCurlResponse($code)) {
                return;
            }
            $Response = json_decode($out, true);
           
    }
    private function CheckCurlResponse($code) {
        $code = (int) $code;
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable'
        );
        try {
            #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
            if ($code != 200 && $code != 204)
                throw new \yii\base\Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        } catch (\yii\base\Exception $E) {
            return false;
        }
        return true;
    }
    public function actionAjaxWizardOff() {
        $user = Yii::$app->user->identity;
        $organization = $user->organization;
        if (Yii::$app->request->isAjax) {
            $organization->step = Organization::STEP_TUTORIAL;
            $organization->save();
            $user->sendWelcome();
            $result = true;
            if($organization->locality == 'Москва'){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                $result = ["result" => "moscow"];
            }
            return $result;
        }
        return false;
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
