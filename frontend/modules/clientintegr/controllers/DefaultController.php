<?php

namespace frontend\modules\clientintegr\controllers;

use Yii;
use common\models\Organization;
use frontend\modules\clientintegr\modules\rkws\components\ServiceHelper;
use common\components\AccessRule;
use yii\filters\AccessControl;
use common\models\Role;
use common\models\RkActions;
use api\common\models\RkService;


/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 */

class DefaultController extends \frontend\controllers\DefaultController {

    public $enableCsrfValidation = false;

    protected $authenticated = false;

    protected $mercCategoryLog = 'merc_log';

    private $sessionId = '';
    private $username;
    private $password;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        /*'actions' => [
                            '*',
                        ],*/
                        'allow' => false,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_BUYER,
                            Role::ROLE_RESTAURANT_JUNIOR_BUYER,
                            Role::ROLE_RESTAURANT_ORDER_INITIATOR,
                        ],
                    ],
                    [
                        'allow' => TRUE,
                        'roles' => ['@'],
                    ],

                ],
            ],
        ];
    }


    public function actionIndex() {

        return $this->render('index');

    }

    protected function setLayout($orgType) {
        switch ($orgType) {
            case Organization::TYPE_RESTAURANT:
                $this->layout = '@frontend/views/layouts/main-client.php';
                break;
            case Organization::TYPE_SUPPLIER:
                $this->layout = '@frontend/views/layouts/main-vendor.php';
                break;
        }
    }

    /**
     * Soap authorization
     * @return mixed result of auth
     * @soap
     */

    public function OpenSession() {

        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($this->username))
        {
            header('WWW-Authenticate: Basic realm="f-keeper.ru"');
            header('HTTP/1.0 401 Unauthorized');
            header('Warning: WSS security in not provided in SOAP header');
            exit;

        } else {

            // $identity = new UserIdentity($this->username, $this->password);

            if (($this->username != 'cyborg') || ($this->password != 'mypass'))
            {
                return 'Auth error. Login or password is not correct.';
            } else {

                $sessionId = Yii::$app->getSecurity()->generateRandomString();
                // $sessionId = md5(uniqid(rand(),1));

                return 'OK_SOPENED:'.$sessionId;
            }

        }

    }

    public function security($header) {


        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($header->UsernameToken->Username)) // Проверяем послали ли нам данные авторизации (BASIC)
        {
            header('WWW-Authenticate: Basic realm="fkeeper.ru"'); // если нет, даем отлуп - пришлите авторизацию
            header('HTTP/1.0 401 Unauthorized');
            exit;
        } else {
            $this->username = $header->UsernameToken->Username;
            $this->password = $header->UsernameToken->Password;
            return $header;
        }
    }

    public function actionGetws() {

        $res = new ServiceHelper();
        $res->getObjects();

        $action = RkActions::find()->where(['id' => 1])->one();
        $action->created = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        if (!$action->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.action.not.save', ['ru' => 'Запрос актуальных данных о лицензиях UCS сохранить не удалось.']));
        }
        $service = RkService::find()->where(['code' => '199990046'])->one();
        if($service->td =='0001-01-05 00:00:00') {
            $service->td = '2100-01-01 00:00:00';
            if (!$service->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.rkws.service.not.save', ['ru' => 'Сведения о лицензии UCS сохранить не удалось.']));
            }
        }

        $this->redirect('index');

    }

    protected function getErrorText($e)
    {
        if ($e->getCode() == 600) {
            return "При обращении к api Меркурий возникла ошибка. Ошибка зарегистрирована в журнале за номером №" . $e->getMessage() . ". Если ошибка повторяется обратитесь в техническую службу.";
        } else {
            Yii::error($e->getMessage()." ".$e->getTraceAsString(), $this->mercCategoryLog);
            return "При обращении к api Меркурий возникла ошибка. Если ошибка повторяется обратитесь в техническую службу.";
        }
    }

}
