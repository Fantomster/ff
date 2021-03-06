<?php

namespace api_web\modules\integration\classes;

use api_web\components\Registry;
use api_web\modules\integration\classes\sync\AbstractSyncFactory;
use api_web\components\WebApi;
use yii\web\BadRequestHttpException;

class SyncServiceFactory extends WebApi
{
    /** @var array */
    public $syncResult = [];

    const TASK_SYNC_GET_LOG = 'get-log';
    /**
     * Construct method for Class SyncServiceFactory
     *
     * @param mixed  $serviceId      Service ID
     * @param array  $params         Transaction params
     * @param string $callbackTaskId Callback task id
     * @throws BadRequestHttpException
     */
    public function __construct($serviceId = 0, array $params = [], string $callbackTaskId = null)
    {
        parent::__construct();
        # 2. Identify Service ID or CALLBACK
        if (!$callbackTaskId) {
            # 2.1.1. Identify Service ID
            if (!array_key_exists($serviceId, Integration::$service_map)) {
                throw new BadRequestHttpException("empty_param|params");
            }
            # 2.1.2. Use entity class (by factory)
            $entity = $this->factory((int)$serviceId, (string)Integration::$service_map[$serviceId]);
            # 2.1.3. Load dictionary data
            /** AbstractSyncFactory $entity */
            $this->syncResult = $entity->loadDictionary($params);
        } elseif ($callbackTaskId == self::TASK_SYNC_GET_LOG) {
            return; //ололо я водитель нло
        } else {
            # 2.2.1. Find service ID and other params by task_id
            $serviceName = self::getServiceMappingNameCallback($callbackTaskId);
            if (!$serviceName) {
                throw new BadRequestHttpException("Service was not recognized by task_id!");
            }
            $serviceId = array_search($serviceName, Integration::$service_map);
            if (!$serviceId) {
                throw new BadRequestHttpException("empty_param|service_id");
            }
            # 2.2.2. Use entity class (by factory)
            $entity = $this->factory((int)$serviceId, $serviceName);
            # 2.1.3. Load dictionary data
            /** AbstractSyncFactory $entity */
            $this->syncResult = $entity->getObjects();
        }
    }

    /**
     * Service Class Factory
     *
     * @param int    $serviceId   Service ID
     * @param string $serviceName Service name
     * @return AbstractSyncFactory
     * @throws BadRequestHttpException
     */
    public function factory(int $serviceId, string $serviceName = null): AbstractSyncFactory
    {
        return self::init($serviceId, $serviceName);
    }

    /**
     * Service Class Factory
     *
     * @param int    $serviceId   Service ID
     * @param string $serviceName Service name
     * @return AbstractSyncFactory
     * @throws BadRequestHttpException
     */
    public static function init(int $serviceId, string $serviceName = null): AbstractSyncFactory
    {
        if (!$serviceName) {
            $serviceName = (string)Integration::$service_map[$serviceId];
        }

        $className = __NAMESPACE__ . '\\sync\\Service' . $serviceName;
        if (class_exists($className)) {
            return new $className($serviceName, $serviceId);
        } else {
            throw new BadRequestHttpException("class_not_exist");
        }
    }

    /**
     * @param $callbackId
     * @return mixed|null
     */
    private static function getServiceMappingNameCallback($callbackId)
    {
        $r = [
            'get-rkws-objects' => Integration::$service_map[Registry::RK_SERVICE_ID],
        ];

        return $r[$callbackId] ?? null;
    }
}
