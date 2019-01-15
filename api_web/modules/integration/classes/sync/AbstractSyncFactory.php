<?php

/**
 * Class AbstractSyncFactory
 *
 * @package   api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author    Mixcart
 * @module    WEB-API
 * @version   2.0
 */

namespace api_web\modules\integration\classes\sync;

use api_web\components\WebApi;
use api_web\helpers\WebApiHelper;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use yii\web\BadRequestHttpException;

abstract class AbstractSyncFactory extends WebApi
{

    const XML_LOAD_RESULT_FAULT = 'Error!';
    const XML_LOAD_RESULT_SUCCESS = 'Success!';

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
    public $dictionaryAvailable = [
        self::DICTIONARY_AGENT,
        self::DICTIONARY_PRODUCT,
        self::DICTIONARY_STORE,
        self::DICTIONARY_UNIT,
        self::DICTIONARY_CATEGORY
    ];

    /** @var string $index Символьный идентификатор справочника */
    public $index;

    /** service_id $_POST params */
    public $serviceId;
    /** Service Name identified by service_id in $_POST params and SyncServiceFactory->$allServicesMap */
    public $serviceName;
    
    protected $logCategory = "sync_log";

    /**
     * Construct method for Class SyncServiceFactory
     *
     * @param string $serviceName Service name
     * @param int    $serviceId   Service name
     */
    public function __construct(string $serviceName, int $serviceId = null)
    {
        parent::__construct();
        $this->serviceName = $serviceName;
        if ($serviceId) {
            $this->serviceId = $serviceId;
        }
    }
    
    /**
     * @param string $message
     */
    public function log($message)
    {
        \Yii::info($message, $this->logCategory);
    }

    /**
     * @param int $service_id
     * @param int $org_id
     * @return OrganizationDictionary
     * @throws BadRequestHttpException
     */
    public function getOrganizationDictionary(int $service_id, int $org_id): OrganizationDictionary
    {

        $outerDic = OuterDictionary::findOne(['service_id' => $service_id, 'name' => $this->index]);
        if (!$outerDic) {
            throw new BadRequestHttpException("outer_dic_not_found");
        }

        $orgDic = OrganizationDictionary::findOne(['outer_dic_id' => $outerDic->id, 'org_id' => $org_id]);
        if (empty($orgDic)) {
            $orgDic = new OrganizationDictionary([
                'outer_dic_id' => $outerDic->id,
                'org_id'       => $org_id,
                'status_id'    => OrganizationDictionary::STATUS_DISABLED,
                'count'        => 0
            ]);

            if (!$orgDic->save()) {
                throw new BadRequestHttpException("org_dic_not_accessible");
            }
        }

        return $orgDic;
    }

    /**
     * @return array
     */
    public function getObjects(): array
    {
        if (method_exists($this, 'sendRequestForObjects')) {
            return $this->sendRequestForObjects();
        }
        return [];
    }

    /**
     * Basic integration method "Load dictionary"
     *
     * @param array $params
     * @return array
     * @throws BadRequestHttpException
     */
    public function loadDictionary(array $params): array
    {
        # 1. Initialize new procedure "Load dictionary"
        if (!isset($params['dictionary'])) {
            throw new BadRequestHttpException("empty_param|dictionary");
        }

        # 2. Use entity class (by factory)
        $entity = $this->factory($params['dictionary'], $this->serviceId);

        # 3. Make transaction "Send request"
        if (method_exists($entity, 'sendRequest')) {
            $requestParams = $params ?? [];
            if (isset($params['product_group'])) {
                $requestParams['product_group'] = $params['product_group'];
            }
            if (isset($params['code'])) {
                $requestParams['code'] = $params['code'];
            }
            return $entity->sendRequest($requestParams);
        } else {
            throw new BadRequestHttpException("method_not_exist");
        }
    }

    /**
     * Send data using Curl
     *
     * @param string $sendUrl  URL
     * @param string $sendData Data
     * @param string $cookie   Data
     * @return string?
     * @throws BadRequestHttpException
     */
    public function sendByCurl(string $sendUrl, string $sendData, string $cookie = null): ?string
    {
        # 1. Check if curl connection params are not empty
        if ($sendUrl && $sendData) {
            # 1.1.1. Prepare curl headers
            $headers = [
                "Content-type: application/xml; charset=utf-8",
                "Content-length: " . strlen($sendData),
                "Connection: close",
            ];
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
            } else {
                curl_setopt($ch, CURLOPT_HEADER, 1);
                // Раскомментировать в случае дебага, иначе header лезет в $data строкой и не получается XML
                // curl_setopt($ch, CURLOPT_STDERR, $fp); // (xsupervisor 04.07.2017)
            }

            # 1.1.4. Exercute curl
            $data = curl_exec($ch);
            $info = curl_getinfo($ch);
            if ($info['http_code'] == self::HTTP_CODE_OK) {
                return $data;
            } else {
                return null;
            }
        } else {
            throw new BadRequestHttpException("curl_params_bad");
        }
    }

    /**
     * ServiceMethod Class Factory
     *
     * @param string $dictionary Dictionary name
     * @param int    $serviceId  Service ID
     * @return AbstractSyncFactory?
     * @throws BadRequestHttpException
     */
    public function factory(string $dictionary, int $serviceId): ?AbstractSyncFactory
    {
        $className = __NAMESPACE__ . '\\' . $this->serviceName . ucfirst($dictionary);
        if (class_exists($className)) {
            return new $className($this->serviceName, $serviceId);
        } else {
            throw new BadRequestHttpException("class_not_exist");
        }
    }

    /**
     * Отправка запроса, обязательный метод
     *
     * @param $params array
     * @return array
     */
    abstract public function sendRequest(array $params = []): array;

    /**
     * @param $request
     * @return array
     */
    public function checkConnect($request = [])
    {
        return ['Не определена функция проверки соединения в классе: ' . get_class($this)];
    }

    /**
     * Метод отправки накладной
     *
     * @param array $request
     * @return array
     */
    public function sendWaybill($request): array
    {
        return ['Не определена функция отправки накладной в классе: ' . get_class($this)];
    }

    /**
     * @param $items
     * @return \Generator
     */
    public function iterator($items)
    {
        foreach ($items as $item) {
            yield $item;
        }
    }

    /**
     * Ответ на запрос синхронизации
     *
     * @param OrganizationDictionary $model
     * @return array
     */
    protected function prepareModel($model)
    {
        $defaultStatusText = OrganizationDictionary::getStatusTextList()[OrganizationDictionary::STATUS_DISABLED];
        return [
            'id'          => $model->id,
            'name'        => $model->outerDic->name,
            'title'       => \Yii::t('api_web', 'dictionary.' . $model->outerDic->name),
            'count'       => $model->count ?? 0,
            'status_id'   => $model->status_id ?? 0,
            'status_text' => $model->statusText ?? $defaultStatusText,
            'created_at'  => WebApiHelper::asDatetime($model->created_at),
            'updated_at'  => WebApiHelper::asDatetime($model->updated_at),
        ];
    }

}
