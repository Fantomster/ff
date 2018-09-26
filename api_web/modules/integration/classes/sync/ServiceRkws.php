<?php

/**
 * Class ServiceRkws
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

use api\common\models\RkService;
use Yii;
use yii\db\mssql\PDO;
use common\models\OuterTask;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use api\common\models\RkAccess;
use api\common\models\RkSession;
use api\common\models\RkServicedata;
use api_web\modules\integration\classes\SyncLog;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;

class ServiceRkws extends AbstractSyncFactory
{

    /** List of dictionaries awailable for a service - By default it is an empty array */
    public $dictionaryAvailable = [
        self::DICTIONARY_AGENT,
        self::DICTIONARY_CATEGORY,
        self::DICTIONARY_PRODUCT,
        self::DICTIONARY_UNIT,
        self::DICTIONARY_STORE,
    ];

    /** @var $licenseCode string License record CODE */
    public $licenseCode;
    /** @var $licenseMixcartId string License record ID */
    public $licenseMixcartId;

    /** @var $now string */
    public $now;

    public $index;

    public $urlCmdInit = 'http://ws.ucs.ru/WSClient/api/Client/Cmd';
    public $urlLoginInit = 'http://ws.ucs.ru/WSClient/api/Client/Login';

    public $urlCmd;
    public $urlLogin;

    const COOK_AUTH_PREFIX_SESSION = '.ASPXAUTH';
    const COOK_AUTH_PREFIX_LOGIN = '_ASPXAUTH';
    const COOK_AUTH_STR_BEGIN = 'Set-Cookie';

    /**
     * Basic service method "Send request"
     * @return array?
     * @throws BadRequestHttpException
     */
    public function sendRequestForObjects(): ?array {
        # 1. Start "Send request" action
        SyncLog::trace('Initialized new procedure action "Send request" in ' . __METHOD__);
        $cook = $this->prepareServiceWithAuthCheck();

        $url = $this->getUrlCmd();
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <RQ cmd="get_objects">
          <PARAM name="start" val="1"/>
          <PARAM name="limit" val="100"/>
          <PARAM name="onlyactive" val="0"/>
        </RQ>';
        $xmlData = $this->sendByCurl($url, $xml, self::COOK_AUTH_PREFIX_SESSION . "=" . $cook . ";");

        print_r($xmlData); exit;

    }

    public function sendRequest(): array
    {

        # 1. Start "Send request" action
        SyncLog::trace('Initialized new procedure action "Send request" in ' . __METHOD__);
        $cook = $this->prepareServiceWithAuthCheck();

        # 2. Если нет сессии - завершаем с ошибкой
        if (!$cook) {
            SyncLog::trace('Cannot authorize with session or login data');
            throw new BadRequestHttpException('Cannot authorize with curl');
        }

        $url = $this->getUrlCmd();
        $guid = UUID::uuid4();
        $xml = $this->prepareXmlWithTaskAndServiceCode($this->index, $this->licenseCode, $guid);
        $xmlData = $this->sendByCurl($url, $xml, self::COOK_AUTH_PREFIX_SESSION . "=" . $cook . ";");
        if ($xmlData) {
            $xml = (array)simplexml_load_string($xmlData);
            if (isset($xml['@attributes']['taskguid']) && isset($xml['@attributes']['code']) && $xml['@attributes']['code'] == 0) {
                $transaction = $this->createTransaction();
                $task = new OuterTask([
                    'service_id' => $this->serviceId,
                    'retry' => 0,
                    'org_id' => $this->user->organization_id,
                    'inner_guid' => $guid,
                    'salespoint_id' => (string)$this->licenseMixcartId,
                    'int_status_id' => OuterTask::STATUS_REQUESTED,
                    'outer_guid' => $xml['@attributes']['taskguid'],
                    'broker_version' => $xml['@attributes']['version'],
                    'oper_code' => $xml['@attributes']['code'],
                ]);
                if ($task->save()) {
                    $transaction->commit();
                    SyncLog::trace('SUCCESS. json-response-data: ' .
                        str_replace(',', PHP_EOL . '      ', json_encode($task->attributes)));
                    return [
                        'task_id' => $task->id,
                        'task_status' => $task->int_status_id,
                    ];
                }
                $transaction->rollBack();
                SyncLog::trace('Cannot save task!');
                throw new BadRequestHttpException('rkws_task_save_error');
            }
        }
        SyncLog::trace('Service connection parameters for final transaction are wrong');
        throw new BadRequestHttpException('empty_service_response_for_transaction');
    }

    public function prepareServiceWithAuthCheck(): ?string
    {

        # 1. Check if authorization is required && active license exists
        SyncLog::trace('Begin "auth check" in ' . __METHOD__);
        $this->now = date('Y-m-d H:i:s', time());

        # 2. Find license Mixcart data
        $licenseMixcart = RkServicedata::findOne(['org' => $this->user->organization_id, 'status_id' => 1]);
        if (!$licenseMixcart || $licenseMixcart->td <= $this->now) {
            SyncLog::trace('Mixcart licence record with active state not found!');
            throw new BadRequestHttpException('no_active_mixcart_license');
        }

        # 3. Find license Rkeeper data
        $license = RkService::findOne([
            'id' => $licenseMixcart->service_id,
            'org' => $this->user->organization_id,
            'status_id' => 1,
            'is_deleted' => 0
        ]);
        if (!$license || !$license->code || ($license->td <= $this->now)) {
            SyncLog::trace('RKeeper licence record with active state not found!');
            throw new BadRequestHttpException('no_active_rkeeper_license');
        }

        # 3. Remember license codes
        $this->licenseCode = $license->code;
        $this->licenseMixcartId = $licenseMixcart->id;

        # 5. Фиксируем активную лицензия найдена и инициализируем транзакции в БД
        SyncLog::trace('Service licence record for organization #' . $this->user->organization_id .
            ' was found (Service code and final date are ' . $license->code . '/' . $license->td . ')');
        SyncLog::trace('Mixcart licence record for organization #' . $this->user->organization_id .
            ' was found (License ID and final date are ' . $licenseMixcart->id . '/' . $licenseMixcart->td . ')');
        $transaction = $this->createTransaction();

        # 6. Пытаемся найти активную сессию и если все хорошо - то используем ее
        $sess = RkSession::findOne(['acc' => $this->user->organization_id, 'status' => 1]);
        if ($sess && $sess->cook) {

            # 6.1. Активная лицензия найдена - проверяем сессию в куки
            SyncLog::trace('Service licence session with active state found with cook: [' .
                substr($sess->cook, 0, 16) . '...]');
            $cookie = self::COOK_AUTH_PREFIX_SESSION . "=" . $sess->cook . ";";
            $xmlData = $this->sendByCurl($this->getUrlCmd(), $this->prepareXmlForTestConnection($this->licenseCode), $cookie);

            if ($xmlData) {
                $xml = (array)simplexml_load_string($xmlData);
                if (isset($xml['OBJECTINFO'])) {
                    $xml = (array)$xml['OBJECTINFO'];
                    $err = (isset($xml['ERROR']) && $xml['ERROR']) ? $xml['ERROR'] : null;
                    if (isset($xml['@attributes']['id']) && $xml['@attributes']['id'] == $license->code && !$err) {

                        # 6.1.1. Активная сессия в куки подтверждена - используем ее и прекращаем процедуры
                        SyncLog::trace('Service licence session with active state id good - use it');
                        /** @var PDO $transaction */
                        $transaction->rollback();
                        return $sess->cook;
                    }
                }
            }
            $this->deactivateSessionWithoutCommit($sess, $transaction);
        }

        # 7. Если сюда попали - то активной сессии нет!!!
        # Пытаемся создать новую
        # Checkout existing valig connection params
        $access = RkAccess::findOne(['locked' => 0]);
        if ($access) {
            SyncLog::trace('Service licence connection parameters found - try to use it');

            # 7.1. Try to prepare new session
            $xmlData = $this->sendByCurl($this->getUrlLogin(), $this->prepareXmlWithAuthParams($access));
            if ($xmlData) {

                preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $xmlData, $matches);
                $cookList = array();
                foreach ($matches[1] as $item) {
                    parse_str($item, $cook);
                    $cookList = array_merge($cookList, $cook);
                }
                if (isset($cookList[self::COOK_AUTH_PREFIX_LOGIN]) && $cookList[self::COOK_AUTH_PREFIX_LOGIN]) {

                    # 7.1.1. Try to save new session
                    $sess = new RkSession();
                    $sess->cook = $cookList[self::COOK_AUTH_PREFIX_LOGIN];
                    $sess->fd = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                    $sess->td = Yii::$app->formatter->asDate('2030-01-01 23:59:59', 'yyyy-MM-dd HH:mm:ss');
                    $sess->acc = $this->user->organization_id;
                    $sess->status = 1;
                    $sess->fid = 1;
                    $sess->ver = 1;
                    $sess->status = 1;
                    if (!$sess->save()) {
                        $transaction->rollback();
                        SyncLog::trace('New session could not be created');
                        throw new BadRequestHttpException('rkws_session_create_error');
                    }

                    # 7.2. Use valid session
                    $transaction->commit();
                    SyncLog::trace('Active session was just created - use it');
                    return $sess->cook;
                }

                $transaction->rollback();
                SyncLog::trace('No session code created');
                throw new BadRequestHttpException('rkws_session_no_cookie');

            } else {
                SyncLog::trace('Service connection parameters are wrong');
                throw new BadRequestHttpException('empty_service_response');
            }

        }
        SyncLog::trace('Empty service connection params');
        throw new BadRequestHttpException('empty_service_access_params');

    }

    public function deactivateSessionWithoutCommit(RkSession $sess, Transaction $transaction)
    {
        # 6.2. Активная сессия в куки не подтверждена
        SyncLog::trace('Service licence session with active state id bad - deactivate it');
        $sess->status = 0;
        $sess->td = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        if (!$sess->save()) {
            $transaction->rollback();
            SyncLog::trace('Fault session could not be deactivated');
            throw new BadRequestHttpException('rkws_session_update_error');
        } else {
            SyncLog::trace('Fault session was just deactivated');
        }
    }

    public function createTransaction(): Transaction
    {
        $app = Yii::$app;
        /** @var Object $app */
        $pdo = $app->db_api;
        /** @var PDO $pdo */
        $transaction = $pdo->beginTransaction();
        /** @var $transaction Transaction */
        return $transaction;
    }

    /**
     * Prepare URL to test service connection with session is active
     * @return string?
     */
    public function getUrlCmd(): ?string
    {
        if (!$this->urlCmd) {
            $url = $this->urlCmdInit;
            if (isset(Yii::$app->params['rkeepCmdURL']) && Yii::$app->params['rkeepCmdURL']) {
                $url = Yii::$app->params['rkeepCmdURL'];
                SyncLog::trace('Upade request url from app:params: ' . $url);
            } else {
                SyncLog::trace('Upade request url from service config: ' . $url);
            }
            $this->urlCmd = $url;
        } else {
            SyncLog::trace('Use previously used request url: ' . $this->urlCmd);
        }
        return $this->urlCmd;
    }

    /**
     * Prepare URL to test service connection with login params
     * @return string?
     */
    public function getUrlLogin(): string
    {
        if (!$this->urlLogin) {
            $url = $this->urlLoginInit;
            if (isset(Yii::$app->params['rkeepAuthURL']) && Yii::$app->params['rkeepAuthURL']) {
                $url = Yii::$app->params['rkeepAuthURL'];
                SyncLog::trace('Upade request url from app:params: ' . $url);
            } else {
                SyncLog::trace('Upade request url from service config: ' . $url);
            }
            $this->urlLogin = $url;
        } else {
            SyncLog::trace('Use previously used request url: ' . $this->urlCmd);
        }
        return $this->urlLogin;
    }

    /**
     * Prepare Xml to test service connection with session is active
     * @param string $code
     * @return string
     */
    public function prepareXmlForTestConnection(string $code): string
    {
        SyncLog::trace('Prepare XML-data type "Service test" in ' . __METHOD__);
        return '<?xml version="1.0" encoding="utf-8" ?>
    <RQ cmd="get_objectinfo">
        <PARAM name="object_id" val="' . $code . '" />
    </RQ>';
    }

    /**
     * Prepare Xml to test service connection session with login params
     * @param $access RkAccess
     * @return string
     */
    public function prepareXmlWithAuthParams(RkAccess $access): string
    {
        SyncLog::trace('Prepare XML-data type "Service new login and password connection" in ' . __METHOD__);
        return '<?xml version="1.0" encoding="UTF-8"?><AUTHCMD key="' .
            $access->lic . '" usr="' . base64_encode($access->login . ';' .
                strtolower(md5($access->login . $access->password)) . ';' .
                strtolower(md5($access->token))) . '" />';
    }


    public function getCallbackURL($dictionary): string
    {
        return Yii::$app->params['rkeepCallBackURL'] . '/' . $dictionary;
    }

    public function prepareXmlWithTaskAndServiceCode($index, $code, $guid): string
    {
        $cb = $this->getCallbackURL($index) . '/?' . AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER . '=' . $guid;
        $cb = 'http://bklv.ru/mc.php?' . AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER . '=' . $guid;
        SyncLog::trace('Callback url is: ' . $cb);
        return '<?xml version="1.0" encoding="utf-8"?>
<RQ cmd="sh_get_corrs" tasktype="any_call" callback="' . $cb . '">
    <PARAM name="object_id" val="' . $code . '"/>
</RQ>';
    }

    /**
     * Метод отправки накладной
     * @return array
     */
    public function sendWaybill(): array
    {

    }
}