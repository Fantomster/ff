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

use api_web\components\WebApi;
use api_web\modules\integration\classes\SyncLog;
use yii\web\BadRequestHttpException;

abstract class AbstractSyncFactory extends WebApi
{

    /** URL $_GET parameter for outer task_guuid */
    const CALLBACK_TASK_IDENTIFIER = 't';

    /** Dictionary name for RKeeper agents data */
    const DICTIONARY_AGENT = 'agent';
    /** Dictionary name for RKeeper categories data */
    const DICTIONARY_CATEGORY = 'category';
    /** Dictionary name for RKeeper products data */
    const DICTIONARY_PRODUCT = 'product';
    /** Dictionary name for RKeeper units data */
    const DICTIONARY_UNIT = 'unit';
    /** Dictionary name for RKeeper storehouses data */
    const DICTIONARY_STORE = 'store';

    /** Valid only HTTP-code for curl resqponse */
    const HTTP_CODE_OK = 200;

    /** List of dictionaries awailable for a service - By default it is an empty array */
    public $dictionaryAvailable = [];

    /** service_id $_POST params */
    public $serviceId;
    /** Service Name identified by service_id in $_POST params and SyncServiceFactory->$allServicesMap */
    public $serviceName;

    /**
     * Construct method for Class SyncServiceFactory
     * @param string $serviceName Service name
     * @param int $serviceId Service name
     */
    public function __construct(string $serviceName, int $serviceId = null)
    {
        parent::__construct();
        $this->serviceName = $serviceName;
        if ($serviceId) {
            $this->serviceId = $serviceId;
        }
    }

    public function getObjects(array $params):array {

        $entity = $this->factory($params['dictionary']);
        if (method_exists($entity, 'sendRequestForObjects')) {
            return $entity->sendRequestForObjects();
        }
        return [];
    }


    /**
     * Basic integration method "Load dictionary"
     * @param array $params
     * @return array
     * @throws BadRequestHttpException
     */
    public function loadDictionary(array $params): array
    {

        # 1. Initialize new procedure "Load dictionary"
        if (isset($params['dictionary'])) {
            SyncLog::trace('Initialized new procedure "Load dictionary" in ' . __METHOD__);
            if (!$this->dictionaryAvailable || !in_array($params['dictionary'], $this->dictionaryAvailable)) {
                SyncLog::trace('"param[dictionary]" is not valid!');
                throw new BadRequestHttpException("param_not_valid|param[dictionary]");
            } else {
                SyncLog::trace('Validated dictionary name (specified in params): "' . $params['dictionary'] . '"');
            }
        } else {
            SyncLog::trace('"param[dictionary]" is required and empty!');
            throw new BadRequestHttpException("empty_param|param[dictionary]");
        }

        # 2. Use entity class (by factory)
        $entity = $this->factory($params['dictionary']);
        SyncLog::trace('Initialized entity class: ' . get_class($entity));

        # 3. Make transaction "Send request"
        if (method_exists($entity, 'sendRequest')) {
            SyncLog::trace('Target method "sendRequest" in the dictionary class "' . get_class($entity) . '" exist');
            return $entity->sendRequest();
        } else {
            SyncLog::trace('Target method "sendRequest" in the dictionary class does not exist!');
            throw new BadRequestHttpException("method_not_exist");
        }
    }

    /**
     * Send data using Curl
     * @param string $sendUrl URL
     * @param string $sendData Data
     * @param string $cookie Data
     * @return string?
     * @throws BadRequestHttpException
     */
    public function sendByCurl(string $sendUrl, string $sendData, string $cookie = null): ?string
    {
        # 1. Check if curl connection params are not empty
        if ($sendUrl && $sendData) {

            # 1.1.1. Prepare curl headers
            $headers = array(
                "Content-type: application/xml; charset=utf-8",
                "Content-length: " . strlen($sendData),
                "Connection: close",
            );
            SyncLog::trace('Curl headers were just prepared (length: ' . strlen($sendData));

            # 1.1.2. Init curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $sendUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $sendData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_VERBOSE, true);

            # 1.1.3. Use session code in cookie or use login acess params in post data
            if ($cookie) {
                curl_setopt($ch, CURLOPT_COOKIE, $cookie);
                SyncLog::trace('Curl options include cookie session code [' . substr($cookie, 0, 24) . '...]');
            } else {
                curl_setopt($ch, CURLOPT_HEADER, 1);
                // Раскомментировать в случае дебага, иначе header лезет в $data строкой и не получается XML
                // curl_setopt($ch, CURLOPT_STDERR, $fp); // (xsupervisor 04.07.2017)
                SyncLog::trace('Curl options use access params in data post!');
            }

            # 1.1.4. Exercute curl
            $data = curl_exec($ch);
            $info = curl_getinfo($ch);
            if ($info['http_code'] == self::HTTP_CODE_OK) {
                SyncLog::trace('Curl was just executed with HTTP CODE ' . $info['http_code']);
                return $data;
            } else {
                SyncLog::trace('Curl was just executed with bad http code: ' . $info['http_code']);
                return null;
            }

        } else {

            # 1.2.1. Fix empty params
            SyncLog::trace('Curl data for old session error: curl content or curl url is empty');
            throw new BadRequestHttpException("curl_params_bad");
        }
    }

    /**
     * ServiceMethod Class Factory
     * @param string $dictionary Dictionary name
     * @return AbstractSyncFactory?
     * @throws BadRequestHttpException
     */
    public function factory(string $dictionary): ?AbstractSyncFactory
    {
        $className = __NAMESPACE__ . '\\' . $this->serviceName . ucfirst($dictionary);
        if (class_exists($className)) {
            return new $className($this->serviceName);
        } else {
            SyncLog::trace('The requested dictionary class "' . $this->serviceName . ucfirst($dictionary) . '"does not exist!');
            throw new BadRequestHttpException("class_not_exist");
        }
    }

}