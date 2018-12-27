<?php

namespace api\modules\v1\modules\odinsrest\controllers;

use api\common\models\one_s\OneSContragent;
use api\common\models\one_s\OneSDic;
use api\common\models\one_s\OneSGood;
use api\common\models\one_s\OneSStore;
use api\common\models\one_s\OneSWaybill;
use api\common\models\one_s\OneSWaybillData;
use api\common\models\one_s\OneSСontragent;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\web\Controller;
use api\common\models\one_s\OneSRestAccess;
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
     * @param string $name1
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
     * @return mixed
     * @soap
     */
    public function getWaybills($sessionId)
    {
        if ($this->check_session($sessionId)) {
            $session = ApiSession::findOne(['token' => $sessionId]);
            $organizationID = $session->acc;
            $db = Yii::$app->get('db_api');
            $rows = (new Query())->select(['one_s_waybill.*', 'one_s_waybill_data.*', 'one_s_good.name as one_s_product_name', 'one_s_good.cid as one_s_product_cid', 'one_s_good.parent_id as one_s_product_parent_id', 'one_s_good.measure as one_s_product_measure', 'one_s_store.cid as one_s_store_cid', 'one_s_contragent.cid as one_s_contragent_cid', 'one_s_contragent.inn_kpp as one_s_inn_kpp'])
                ->from('one_s_waybill')
                ->where(['one_s_waybill.org' => $organizationID, 'one_s_waybill.readytoexport' => 1])
                ->leftJoin('one_s_waybill_data', 'one_s_waybill_data.waybill_id = ' . 'one_s_waybill.id')
                ->leftJoin('one_s_good', 'one_s_good.id = ' . 'one_s_waybill_data.product_rid')
                ->leftJoin('one_s_contragent', 'one_s_contragent.id = ' . 'one_s_waybill.agent_uuid')
                ->leftJoin('one_s_store', 'one_s_store.id = ' . 'one_s_waybill.store_id')
                ->all($db);
            foreach ($rows as $row) {
                $productID = $row['product_id'];
                $catalogBaseGood = CatalogBaseGoods::findOne(['id' => $productID]);
                $row['mixcart_product_name'] = $catalogBaseGood->product ?? '';
            }
            \Yii::$app->get('db_api')
                ->createCommand()
                ->update(OneSWaybill::tableName(), [
                    'status_id' => 2
                ], [
                    'org' => $organizationID,
                    'readytoexport' => 1
                ])->execute();
            return json_encode($rows, JSON_UNESCAPED_UNICODE);
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }


    /**
     * Send goods list
     * @param string $sessionId
     * @param string $body
     * @return string
     * @soap
     */
    public function sendGoodsList(String $sessionId, String $body): String
    {
        return $this->handleData($sessionId, $body, 1);
    }


    /**
     * Send stores list
     * @param string $sessionId
     * @param string $body
     * @return string
     * @soap
     */
    public function sendStoresList(String $sessionId, String $body): String
    {
        return $this->handleData($sessionId, $body, 2);
    }


    /**
     * Send contragents list
     * @param string $sessionId
     * @param string $body
     * @return string
     * @soap
     */
    public function sendContragentsList(String $sessionId, String $body): String
    {
        return $this->handleData($sessionId, $body, 3);
    }


    private function handleData(String $sessionId, String $body, int $type): String
    {
        $res = $this->check_session($sessionId);
        if ($res) {
            $content = json_decode($body);
            $positions = $content->data ?? null;
            if (is_iterable($positions)) {
                switch ($type) {
                    case 1:
                        $modelName = OneSGood::className();
                        $dictypeID = 3;
                        break;
                    case 2:
                        $modelName = OneSStore::className();
                        $dictypeID = 2;
                        break;
                    case 3:
                        $modelName = OneSContragent::class;
                        $dictypeID = 1;
                        break;
                    default:
                        $modelName = OneSGood::className();
                        $dictypeID = 1;
                }
                $arr = $this->handlePositions($positions, $modelName, $res, $type);
                $count = $modelName::find()->where(['org_id' => $res])->count();
                $oneSDic = OneSDic::findOne(['org_id' => $res, 'dictype_id' => $dictypeID]);
                if ($oneSDic) {
                    $oneSDic->dicstatus_id = 1;
                    $oneSDic->obj_count = $count ?? 0;
                    $oneSDic->save();
                }
                $decodedResponse = \GuzzleHttp\json_encode($arr);
                $this->save_action(__FUNCTION__, $sessionId, 1, 'OK', $this->ip);
                return $decodedResponse;
            } else {
                $this->save_action(__FUNCTION__, $sessionId, 0, 'No new goods', $this->ip);
                return 'No positions has been uploaded.';
            }
        } else {
            $this->save_action(__FUNCTION__, $sessionId, 0, 'No active session', $this->ip);
            return 'Session error. Active session is not found.';
        }
    }



    private function handlePositions($positions, $modelName, $res, $type)
    {
        $i = 0;
        $returnArray = [];
        foreach ($positions as $position) {
            if (!$position->is_changed) continue;
            $oneSPosition = $modelName::findOne(['org_id' => $res, 'cid' => $position->cid]);
            if (!$oneSPosition) {
                $oneSPosition = new $modelName();
            }
            $oneSPosition->name = $position->name;
            $oneSPosition->cid = $position->cid;
            $oneSPosition->org_id = $res;
            switch ($type) {
                case 1:
                    $oneSPosition->parent_id = $position->parent_id;
                    $oneSPosition->measure = $position->measure;
                    $oneSPosition->is_category = $position->is_category ?? 0;
                    break;
                case 2:
                    $oneSPosition->address = $position->address;
                    break;
                case 3:
                    $oneSPosition->inn_kpp = $position->inn_kpp;
                    break;
                default:
                    $oneSPosition->parent_id = $position->parent_id;
                    $oneSPosition->measure = $position->measure;
            }
            $oneSPosition->updated_at = new Expression('NOW()');
            if ($oneSPosition->validate()) {
                $oneSPosition->save();
            } else {
                // validation failed: $errors is an array containing error messages
                $errors = $oneSPosition->errors;
                $errorsString = "<pre>" . print_r($errors, 1) . "</pre>";
                Yii::error($errorsString);
                return 'Saving failture';
            }
            $i++;
            $returnArray[$i]['cid'] = $oneSPosition->cid;
            $returnArray[$i]['updated_at'] = date('Y-m-d h:i:s');
        }
        $arr = [
            'updated_count' => $i,
            'data' => $returnArray
        ];
        return $arr;
    }


    /**
     * Close session
     * @param string $sessionId
     * @return mixed result
     * @soap
     */
    public function closeSession(String $sessionId)
    {
        if ($this->check_session($sessionId)) {
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
     * @param string $login
     * @param string $pass
     * @return mixed result of auth
     * @soap
     */
    public function openSession(String $login, String $pass)
    {
        $this->username = $login;
        $this->password = $pass;
        if (!$acc = OneSRestAccess::find()->where('login = :username and now() between fd and td', [':username' => $this->username])->one()) {
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
            $sess->acc = $acc->org;
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
                return $sess->token;
            }
        } else {
            $this->save_action(__FUNCTION__, 0, 0, 'Wrong password', $this->ip);
            return 'Auth error. Password is not correct.';
        }
    }

    /**
     * @param $header
     * @return mixed
     */
    public function security($header)
    {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($header->UsernameToken->Username)) // Проверяем послали ли нам данные авторизации (BASIC)
        {
            header('WWW-Authenticate: Basic realm="fkeeper.ru"'); // если нет, даем отлуп - пришлите авторизацию
            header('HTTP/1.0 401 Unauthorized');
            $this->save_action(__FUNCTION__, 0, 0, 'Auth error HTTP/1.0 401 Unauthorized', $this->ip);
        } else {
            $this->username = $header->UsernameToken->Username;
            $this->password = $header->UsernameToken->Password;
            $this->nonce = $header->UsernameToken->Nonce;
            $this->extimefrom = $header->UsernameToken->Created;
            return $header;
        }
    }

    /**
     * @param $session
     * @param $nonce
     * @return int
     */
    public function check_session(String $session)
    {
        $apiSession = ApiSession::find()->where('token = :token and now() between fd and td',
            [':token' => $session])->one();
        if ($apiSession) {
            return $apiSession->acc ?? false;
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
