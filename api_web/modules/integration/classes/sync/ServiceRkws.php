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

use Yii;
use yii\db\mssql\PDO;
use common\models\OuterTask;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use api\common\models\RkAccess;
use api\common\models\RkSession;
use api\common\models\RkServicedata;
use api_web\modules\integration\classes\SyncLog;

class ServiceRkws extends AbstractSyncFactory
{
    public $dictionaryAvailable = [
        self::DICTIONARY_AGENT,
        self::DICTIONARY_CATEGORY,
        self::DICTIONARY_PRODUCT,
        self::DICTIONARY_UNIT,
        self::DICTIONARY_STORE,
    ];

    /** @var $serviceData RkServicedata */
    public $serviceData;

    /** @var $serviceCode string */
    public $serviceCode;

    /** @var $serviceId int */
    public $serviceId = 1;

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
     */
    public function sendRequest(): ?array
    {

        # 1. Start "Send request" action
        SyncLog::fix('Initialized new procedure action "Send request" in ' . __METHOD__);
        $cook = $this->prepareServiceWithAuthCheck();
        if (!$cook) {
            SyncLog::exit('Cannot authorize with curl', 'Cannot authorize with curl');
        }
        if ($this->serviceCode) {
            $url = $this->getUrlCmd();
            $guid = UUID::uuid4();
            $xml = $this->prepareXmlWithTaskAndServiceCode($this->index, $this->serviceCode, $guid);
            $xmlData = $this->sendByCurl($url, $xml, self::COOK_AUTH_PREFIX_SESSION . "=" . $cook . ";");
            if ($xmlData) {
                $xml = (array)simplexml_load_string($xmlData);
                if (isset($xml['@attributes']['taskguid']) && isset($xml['@attributes']['code']) && $xml['@attributes']['code'] == 0) {
                    $app = Yii::$app;
                    /** @var Object $app */
                    $pdo = $app->db_api;
                    /** @var PDO $pdo */
                    $transaction = $pdo->beginTransaction();
                    $task = new OuterTask([
                        'service_id' => $this->serviceId,
                        'retry' => 0,
                        'org_id' => $this->user->organization_id,
                        'inner_guid' => $guid,
                        'salespoint_id' => (string)$this->serviceData->id,
                        'int_status_id' => OuterTask::STATUS_REQUESTED,
                        'outer_guid' => $xml['@attributes']['taskguid'],
                        'broker_version' => $xml['@attributes']['version'],
                        'oper_code' => $xml['@attributes']['code'],
                    ]);
                    if ($task->save()) {
                        /** @var PDO $transaction */
                        $transaction->commit();
                        SyncLog::fix('SUCCESS. json-response-data: '.
                            str_replace(',',  PHP_EOL. '      ', json_encode($task->attributes)));
                        return [
                            'task_id' => $task->id,
                            'task_status' => $task->int_status_id,
                        ];
                    }
                    /** @var PDO $transaction */
                    $transaction->rollBack();
                    SyncLog::exit('Cannot save task!', 'Cannot save task!');
                }
            }
            SyncLog::exit('Service request wass not loaded or broken', 'bad_request');
        }
        SyncLog::exit('Service code is empty!', 'empty_service_code');
        return null;
    }

    public function prepareServiceWithAuthCheck(): ?string
    {

        # 1. Check if authorization is required && active license exists
        SyncLog::fix('Begin "auth check" in ' . __METHOD__);
        $this->now = date('Y-m-d H:i:s', time());
        if (!$this->checkAuth()) {
            # 1.1. Find license data
            $this->serviceData = RkServicedata::findOne(['org' => $this->user->organization_id]);
            $this->serviceCode = $this->serviceData->getCode();
            # 1.2. Check if license expiratioon date is fault
            if (!$this->serviceData || !$this->serviceData->status_id || ($this->serviceData->td <= $this->now)) {
                SyncLog::exit('Service licence active state not found!', 'no_license');
            }
        }
        SyncLog::fix('Active licence for user\'s organization #' . $this->user->organization_id . ' found (Service code and final date are ' . $this->serviceCode . '/' . $this->serviceData->td . ')');

        # 2. Check if licence active state exists - try to use it
        $sess = RkSession::findOne(['acc' => $this->user->organization_id, 'status' => 1]);

        $app = Yii::$app;
        /** @var Object $app */
        $pdo = $app->db_api;
        /** @var PDO $pdo */
        $transaction = $pdo->beginTransaction();

        # 2. Try to use session code or initialize new connection
        if ($sess && $sess->cook) {

            # 2.1. Try to use session code
            SyncLog::fix('Service licence active state found with cook: [' . substr($sess->cook, 0, 16) . '...]');
            $cookie = self::COOK_AUTH_PREFIX_SESSION . "=" . $sess->cook . ";";
            $xmlData = $this->sendByCurl($this->getUrlCmd(), $this->prepareXmlForTestConnection(), $cookie);
            if ($xmlData) {
                $xml = (array)simplexml_load_string($xmlData);
                if (isset($xml['OBJECTINFO'])) {
                    $xml = (array)$xml['OBJECTINFO'];
                    $err = (isset($xml['ERROR']) && $xml['ERROR']) ? $xml['ERROR'] : null;
                    if (isset($xml['@attributes']['id']) && $xml['@attributes']['id'] == $this->serviceCode && !$err) {
                        # 2.1.1. Use valid session code
                        SyncLog::fix('Service licence with active state id good - use it');
                        $transaction->rollback();
                        return $sess->cook;
                    }
                }
            }

            # 2.1.2. Deactivate session with foul session code
            SyncLog::fix('Found licence with foul active state - deactivate it');
            $sess->status = 0;
            $sess->td = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            if (!$sess->save()) {
                SyncLog::exit('Fault session could not be deactivated');
            }

            SyncLog::fix('Licence with foul active state deactivated');

        } else {
            SyncLog::fix('Service licence wit hactive state was not found');
        }
        # 3. Checkout existing valig connection params
        $access = RkAccess::findOne(['fid' => $this->serviceData->id]);
        if (!$access) {
            SyncLog::exit('Empty connection params', 'empty_params');
        }
        SyncLog::fix('Service licence connection parameters found - try to use it');

        # 4. Try to us connection session
        $xmlData = $this->sendByCurl($this->getUrlLogin(), $this->prepareXmlWithAuthParams($access));
        if ($xmlData) {
            preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $xmlData, $matches);
            $cookList = array();
            foreach ($matches[1] as $item) {
                parse_str($item, $cook);
                $cookList = array_merge($cookList, $cook);
            }
            if (isset($cookList[self::COOK_AUTH_PREFIX_LOGIN]) && $cookList[self::COOK_AUTH_PREFIX_LOGIN]) {

                # 4.1. Try to create new session
                $sess = new RkSession();
                $sess->cook = $cookList[self::COOK_AUTH_PREFIX_LOGIN];
                $sess->fd = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                $sess->td = Yii::$app->formatter->asDate('2030-01-01 23:59:59', 'yyyy-MM-dd HH:mm:ss');
                $sess->acc = $this->user->organization_id;
                $sess->status = 1;
                $sess->fid = $this->serviceData->id;
                $sess->ver = 1;
                $sess->status = 1;
                if (!$sess->save()) {
                    SyncLog::exit('New session could not be created');
                }

                # 4.2. Use valid session code
                SyncLog::fix('Active session was just created - use it');
                /** @var PDO $transaction */
                $transaction->commit();
                return $sess->cook;
            }

        }

        # 5. Fix bad connection params
        SyncLog::fix('Service licence connection parameters are wrong');
        return null;
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
                SyncLog::fix('Upade request url from app:params: ' . $url);
            } else {
                SyncLog::fix('Upade request url from service config: ' . $url);
            }
            $this->urlCmd = $url;
        } else {
            SyncLog::fix('Use previously used request url: ' . $this->urlCmd);
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
                SyncLog::fix('Upade request url from app:params: ' . $url);
            } else {
                SyncLog::fix('Upade request url from service config: ' . $url);
            }
            $this->urlLogin = $url;
        } else {
            SyncLog::fix('Use previously used request url: ' . $this->urlCmd);
        }
        return $this->urlLogin;
    }

    /**
     * Prepare Xml to test service connection with session is active
     */
    public function prepareXmlForTestConnection(): string
    {
        SyncLog::fix('Prepare XML-data type "Service test" in ' . __METHOD__);
        return '<?xml version="1.0" encoding="utf-8" ?>
    <RQ cmd="get_objectinfo">
        <PARAM name="object_id" val="' . $this->serviceCode . '" />
    </RQ>';
    }

    /**
     * Prepare Xml to test service connection session with login params
     * @param $access RkAccess
     * @return string
     */
    public function prepareXmlWithAuthParams(RkAccess $access): string
    {
        SyncLog::fix('Prepare XML-data type "Service new login and password connection" in ' . __METHOD__);
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
        SyncLog::fix('Callback url is: '.$cb);
        return '<?xml version="1.0" encoding="utf-8"?>
<RQ cmd="sh_get_corrs" tasktype="any_call" callback="' . $cb . '">
    <PARAM name="object_id" val="' . $code . '"/>
</RQ>';
    }

}