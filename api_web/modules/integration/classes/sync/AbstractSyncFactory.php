<?php

/**
 * Class AbstractSyncFactory
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

use Yii;
use Yii\console\Application as ConsoleApplication;
use api_web\components\WebApi;
use api_web\modules\integration\classes\SyncLog;

abstract class AbstractSyncFactory extends WebApi
{

    const CALLBACK_TASK_IDENTIFIER = 't';

    const DICTIONARY_AGENT = 'agent';
    const DICTIONARY_CATEGORY = 'category';
    const DICTIONARY_PRODUCT = 'product';
    const DICTIONARY_UNIT = 'unit';
    const DICTIONARY_STORE = 'store';

    const HTTP_CODE_OK = 200;

    const DISALLOW_CONSOLE = true;

    const OPER_GET_OBJECTS = 'get_objects';

    public $dictionaryAvailable = [];

    public $serviceName;

    /**
     * Construct method for Class SyncServiceFactory
     * @param string $service Service name
     */
    public function __construct(string $service)
    {
        parent::__construct();
        $this->serviceName = $service;
    }

    /**
     * Basic integration method "Load dictionary"
     * @param array $params
     * @return array
     */
    public function loadDictionary(array $params): array
    {

        # 1. Initialize new procedure "Load dictionary"
        SyncLog::fix('Initialized new procedure "Load dictionary" in ' . __METHOD__);
        if (!isset($params['dictionary'])) {
            SyncLog::exit('"param[dictionary]" is required and empty!', "empty_param|param[dictionary]");
        }
        if (!$this->dictionaryAvailable || !in_array($params['dictionary'], $this->dictionaryAvailable)) {
            SyncLog::exit('"param[dictionary]" is not valid!', "param_not_valid|param[dictionary]");
        }
        SyncLog::fix('Validated dictionary name (specified in params): "' . $params['dictionary'] . '"');

        # 2. Use entity class (by factory)
        $entity = $this->factory($params['dictionary']);
        SyncLog::fix('Initialized entity class: ' . get_class($entity));

        # 3. Make transaction "Send request"
        if (method_exists($entity, 'sendRequest')) {
            SyncLog::fix('Target method "sendRequest" in the dictionary class "'.get_class($entity).'" exist');
            return $entity->sendRequest();
        } else {
            SyncLog::exit('Target method "sendRequest" in the dictionary class does not exist!', "method_not_exist");
        }
        return [];
    }

    /**
     * ServiceMethod Class Factory
     * @param string $dictionary Dictionary name
     * @return AbstractSyncFactory?
     */
    public function factory(string $dictionary): ?AbstractSyncFactory
    {
        $className = __NAMESPACE__ . '\\' . $this->serviceName . ucfirst($dictionary);
        if (class_exists($className)) {
            return new $className($this->serviceName);
        }
        SyncLog::exit("The requested dictionary class does not exist!", "class_not_exist");
        return null;
    }

    /**
     * Check if console application is not allowed
     * @return bool
     */
    public function checkAuth(): bool
    {
        if (Yii::$app instanceof ConsoleApplication) {
            return self::DISALLOW_CONSOLE;
        } elseif (Yii::$app->user->isGuest) {
            return true;
        }
        return false;
    }

    /**
     * Send data using Curl
     * @param string $sendUrl URL
     * @param string $sendData Data
     * @param string ? $cookie Data
     * @return string?
     */
    public function sendByCurl(string $sendUrl, string $sendData, string $cookie = null): ?string
    {
        # 1. Check if curl connection params are not empty
        if (!$sendUrl || !$sendData) {
            SyncLog::exit('Curl data for old session error: curl content or curl url is empty', "curl_params_bad");
        }

        # 2. Prepare curl headers
        $headers = array(
            "Content-type: application/xml; charset=utf-8",
            "Content-length: " . strlen($sendData),
            "Connection: close",
        );
        SyncLog::fix('Curl headers were just prepared (length: ' . strlen($sendData));

        # 3. Init curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sendUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sendData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            SyncLog::fix('Curl options were just prepared - with cookie session code [' . substr($cookie, 0, 24) . '...]');
        } else {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            // Раскомментировать в случае дебага, иначе header лезет в $data строкой и не получается XML
            // curl_setopt($ch, CURLOPT_STDERR, $fp); // (xsupervisor 04.07.2017)
            SyncLog::fix('Curl options were just prepared - with login params in data post!');
        }

        # 3. Exercute curl
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        SyncLog::fix('Curl was just executed with HTTP CODE ' . $info['http_code']);
        if ($info['http_code'] == self::HTTP_CODE_OK) {
            return $data;
        }
        return null;
    }

}