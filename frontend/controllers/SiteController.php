<?php

namespace frontend\controllers;

use common\models\CatalogBaseGoods;
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
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'only'       => ['logout', 'signup', 'index', 'about', 'complete-registration', 'ajax-tutorial-off', 'ajax-tutorial-on', 'faq', 'restaurant', 'supplier', 'unsubscribe'],
                'rules'      => [
                    [
                        'actions' => ['signup', 'index', 'about', 'faq', 'restaurant', 'supplier', 'unsubscribe'],
                        'allow'   => true,
                        'roles'   => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'complete-registration', 'ajax-tutorial-off', 'ajax-tutorial-on', 'ajax-complete-registration', 'ajax-wizard-off', 'unsubscribe'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions'      => ['index', 'about', 'faq', 'restaurant', 'supplier'],
                        'allow'        => false,
                        'roles'        => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_ONE_S_INTEGRATION,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                            Role::getFranchiseeEditorRoles(),
                            Role::ROLE_RESTAURANT_ACCOUNTANT,
                            Role::ROLE_RESTAURANT_BUYER,
                            Role::ROLE_RESTAURANT_JUNIOR_BUYER,
                            Role::ROLE_RESTAURANT_ORDER_INITIATOR,
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
            'verbs'  => [
                'class'   => VerbFilter::className(),
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
                    'name'         => $profile->full_name,
                    'company_name' => $organization->name,
                    'email'        => $user->email,
                    'phone'        => $profile->phone,
                    'city'         => $organization->locality,
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

    public function actionImageBase()
    {
        function printBase64($image)
        {
            $array = explode('base64,', $image);
            return base64_decode($array[1]);
        }

        $id = Yii::$app->request->get('id');
        $type = Yii::$app->request->get('type');
        if ($type == 'product') {
            header('Content-Type: image/png');
            $model = CatalogBaseGoods::findOne($id);
            if (!empty($model)) {
                $image = $model->getImageUrl();
                if (strstr($image, 'base64') !== false) {
                    echo printBase64($image);
                } else {
                    echo file_get_contents($image);
                }
            } else {
                echo printBase64(CatalogBaseGoods::DEFAULT_IMAGE);
            }
            exit;
        }
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

    private function getPage($url, $follow, $cookiesIn = '')
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER         => true, //return headers in addition to content
            CURLOPT_FOLLOWLOCATION => $follow, // follow redirects
            CURLOPT_ENCODING       => "", // handle all encodings
            CURLOPT_AUTOREFERER    => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT        => 120, // timeout on response
            CURLOPT_MAXREDIRS      => 10, // stop after 10 redirects
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_SSL_VERIFYPEER => true, // Validate SSL Certificates
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_COOKIE         => $cookiesIn,
            CURLOPT_HTTPHEADER     => ['User-Agent: Mozilla/5.0 (X11; Ubuntu; Linu…) Gecko/20100101 Firefox/61.0'],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $rough_content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        $header_content = substr($rough_content, 0, $header['header_size']);
        $body_content = trim(str_replace($header_content, '', $rough_content));
        $pattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m";
        preg_match_all($pattern, $header_content, $matches);
        $cookiesOut = implode("; ", $matches['cookie']);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['headers'] = $header_content;
        $header['content'] = $body_content;
        $header['cookies'] = $cookiesOut;
        return $header;
    }

    private function postForm($url, $vars = [], $cookiesIn = '')
    {

        $options = [
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER         => true, //return headers in addition to content
            CURLOPT_FOLLOWLOCATION => false, // follow redirects
            CURLOPT_POST           => true, // handle all encodings
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT        => 120, // timeout on response
            CURLOPT_MAXREDIRS      => 10, // stop after 10 redirects
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_SSL_VERIFYPEER => true, // Validate SSL Certificates
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_COOKIE         => $cookiesIn,
            CURLOPT_POSTFIELDS     => http_build_query($vars),
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $rough_content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        $header_content = substr($rough_content, 0, $header['header_size']);
        $body_content = trim(str_replace($header_content, '', $rough_content));
        $pattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m";
        preg_match_all($pattern, $header_content, $matches);
        $cookiesOut = implode("; ", $matches['cookie']);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['headers'] = $header_content;
        $header['content'] = $body_content;
        $header['cookies'] = $cookiesOut;
        return $header;
    }

    public function actionTestAuth()
    {
        $link = 'https://t2-mercury.vetrf.ru/hs/';

        $step0 = $this->getPage($link, false);

        $step1 = $this->getPage($step0['redirect_url'], true, $step0['cookies']);

        $data = \darkdrim\simplehtmldom\SimpleHTMLDom::str_get_html($step1['content']);

        $forms = $data->find('form');

        $inputs = [];

        $action = $forms[0]->action;
        foreach ($forms[0]->find('input') as $input) {
            $inputs[$input->name] = $input->value;
        }

        $step2 = $this->postForm($action, $inputs, $step0['cookies']);

        $authData = ['j_username' => 'ponitkov_ma_180409', 'j_password' => '2wsx2WSX', '_eventId_proceed' => ''];

        $step3 = $this->postForm($step2['redirect_url'], $authData, $step2['cookies']);

        $data2 = \darkdrim\simplehtmldom\SimpleHTMLDom::str_get_html($step3['content']);

        $forms2 = $data2->find("form");

        $action2 = html_entity_decode($forms2[0]->action);

        $inputs2 = [];

        foreach ($forms2[0]->find('input') as $input) {
            $inputs2[$input->name] = $input->value;
        }

        $step4 = $this->postForm($action2, $inputs2, $step0['cookies']);

        $step5 = $this->getPage($step4['redirect_url'], true, $step4['cookies']);

        $getVsdUrl = 'https://t2-mercury.vetrf.ru/pub/operatorui?_language=ru&_action=showVetDocumentFormByUuid&uuid=b06d3137-befe-46d6-b7d4-ceb3777e8b12';
        $step5 = $this->getPage($getVsdUrl, true);
        $data5 = \darkdrim\simplehtmldom\SimpleHTMLDom::str_get_html($step5['content']);
        $rows = $data5->find('.profile-info-row');
        foreach ($rows as $row) {
            $itemName = $row->find('.profile-info-name')[0];
            if ($itemName->innertext == 'Номер ВСД') {
                $vsdNum = $row->find('.profile-info-value')[0]->find('span')[0]->innertext;
            }
        }

        $urlGetPdf = 'https://t2-mercury.vetrf.ru/hs/operatorui?printType=1&preview=false&_action=printVetDocumentList&_language=ru&printPk=' . $vsdNum . '&displayPreview=false&displayRecipient=true&transactionPk=&vetDocument=&batchNumber=';
        $step7 = $this->getPage($urlGetPdf, true, $step4['cookies']);
        $data7 = $step7['content'];

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        header("Content-type:application/pdf");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        flush();
        echo $data7;

        //return $this->render('test-auth', compact('data6', 'step7', 'vsdNum'));
    }

}
