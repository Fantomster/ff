<?php

namespace api\modules\v1\modules\odinsrest\controllers;

use Yii;
use yii\web\Controller;
use api\common\models\ApiAccess;
use api\common\models\ApiSession;
use api\common\models\ApiActions;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\MpEd;
use common\models\Catalog;
use common\models\RelationSuppRest;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */
class DefaultController extends Controller
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;
    private $username;
    private $password;
    private $nonce;
    private $extimefrom;
    private $ip;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->ip = Yii::$app->request->getUserIP();
            return true;
        }
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'wsdl' => [
                'class' => 'mongosoft\soapserver\Action',
                'serviceOptions' => [
                    'disableWsdlMode' => false,
                ]
            ],
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


    /**
     * Hello
     * @param string $name
     * @return string
     * @soap
     */
    public function getHello($name)
    {
        $this->save_action(__FUNCTION__, 0, 1, 'OK', $this->ip);
        return 'Hello ' . $name . ' IP - ' . $this->ip . '! Server Date:' . gmdate("Y-m-d H:i:s");
    }


    /**
     * Get waybills
     * @param string $sessionId
     * @param string $nonce
     * @return mixed
     * @soap
     */
    public function getWaybills($sessionId, $nonce)
    {

        if ($this->check_session($sessionId, $nonce)) {

            return "OK";

            /*
            if ($lang == 'ENG') {
                $catview = 'api_units_eng_v';
            } else {
                $catview = 'api_units_rus_v';
            }

            $cats = Yii::$app->db_api->createCommand('SELECT fid, denom FROM ' . $catview)
                ->queryAll();

            $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $this->ip);
            return $cats;
            */
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }

    }

    /**
     * Close session
     * @param string $sessionId
     * @param string $nonce
     * @return mixed result
     * @soap
     */
    public function CloseSession($sessionId, $nonce)
    {
        if ($this->check_session($sessionId, $nonce)) {
            $sess = ApiSession::find()->where('token = :token and now() between fd and td',
                [':token' => $sessionId])->one();

            $sess->td = gmdate('Y-m-d H:i:s');
            $sess->status = 2;

            if (!$sess->save()) {
                return $sess->errors;
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $sess->ip);
                return 'OK_CLOSED :' . $sess->token;
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }

    /**
     * Soap authorization open session
     * @return mixed result of auth
     * @soap
     */
    public function OpenSession()
    {

       $this->username =  isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] :  $_SERVER['REDIRECT_REMOTE_USER'];

       $this->password =  $_SERVER['PHP_AUTH_PW'];

       if (empty($this->username) || empty($this->password)) {
            header('WWW-Authenticate: Basic realm="f-keeper.ru"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        } else {
        if ($this->username == "cyborg" && $this->password == "testpass") {
            return "Welcome to MixCart integration, Cyborg";
        } else {
            header('WWW-Authenticate: Basic realm="f-keeper.ru"');
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }

        }

        /*

        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($this->username)) {
            header('WWW-Authenticate: Basic realm="f-keeper.ru"');
            header('HTTP/1.0 401 Unauthorized');
            header('Warning: WSS security in not provided in SOAP header');
            $this->save_action(__FUNCTION__, 0, 0, 'Auth error HTTP/1.0 401 Unauthorized', $this->ip);
        } else {
            if (!$acc = ApiAccess::find()->where('login = :username and now() between fd and td', [':username' => $this->username])->one()) {
                $this->save_action(__FUNCTION__, 0, 0, 'Wrong login', $this->ip);
                return 'Auth error. Login is not found.';
            };

            if (Yii::$app->getSecurity()->validatePassword($this->password, $acc->password)) {

                $sessionId = Yii::$app->getSecurity()->generateRandomString();
                $oldsess = ApiSession::find()->orderBy('fid DESC')->one();
                $sess = new ApiSession();

                if ($oldsess) {
                    $sess->fid = $oldsess->fid + 1;
                } else {
                    $sess->fid = 1;
                }

                $sess->token = $sessionId;
                $sess->acc = $acc->fid;
                $sess->nonce = $this->nonce;
                $sess->fd = gmdate('Y-m-d H:i:s');
                $sess->td = gmdate('Y-m-d H:i:s', strtotime('+1 day'));
                $sess->ver = 1;
                $sess->status = 1;
                $sess->ip = $this->ip;
                $sess->extimefrom = gmdate('Y-m-d H:i:s');

                if (!$sess->save()) {
                    return $sess->errors;
                } else {
                    $this->save_action(__FUNCTION__, $sess->token, 1, 'OK', $this->ip);
                    return 'OK_SOPENED:' . $sess->token;
                }
            } else {
                $this->save_action(__FUNCTION__, 0, 0, 'Wrong password', $this->ip);
                return 'Auth error. Password is not correct.';
            }
        }
        */
    }

    /**
     * @param $header
     * @return mixed
     */
    public function security($header)
    {
/*

        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($header->UsernameToken->Username)) // Проверяем послали ли нам данные авторизации (BASIC)
        {
            header('WWW-Authenticate: Basic realm="fkeeper.ru"'); // если нет, даем отлуп - пришлите авторизацию
            header('HTTP/1.0 401 Unauthorized');
         //   $this->save_action(__FUNCTION__, 0, 0, 'Auth error HTTP/1.0 401 Unauthorized', $this->ip);

        } else {
            $this->username = $header->UsernameToken->Username;
            $this->password = $header->UsernameToken->Password;
            $this->nonce = $header->UsernameToken->Nonce;
            $this->extimefrom = $header->UsernameToken->Created;
            return $header;
        }
*/
    }

    /**
     * @param $session
     * @param $nonce
     * @return bool
     */
    public function check_session($session, $nonce)
    {
        if (ApiSession::find()->where('token = :token and nonce = :nonce and now() between fd and td',
            [':token' => $session, ':nonce' => $nonce])->exists()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $func
     * @param $sess
     * @param $result
     * @param $comment
     * @param $ip
     * @return array|bool
     */
    public function save_action($func, $sess, $result, $comment, $ip)
    {
        $act = new ApiActions;
        $currSess = ApiSession::find()->where('token = :token', [':token' => $sess])->one();

        if ($currSess) {
            $act->session = $currSess->fid;
            $act->ip = $currSess->ip;
        } else {
            $act->session = 0;
            $act->ip = $ip;
        }

        $act->action = $func;
        $act->created = gmdate('Y-m-d H:i:s');
        $act->result = $result;
        $act->comment = $comment;

        if (!$act->save()) {
            return $act->errors;
        } else {
            return true;
        }
    }
}