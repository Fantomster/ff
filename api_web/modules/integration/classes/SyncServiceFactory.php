<?php

/**
 * Class SyncServiceFactory
 * @package api_web\module\integration
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes;

use api_web\modules\integration\classes\sync\AbstractSyncFactory;
use api_web\components\WebApi;
use yii\web\BadRequestHttpException;

class SyncServiceFactory extends WebApi
{

    /** SERVICE RKEEPER name */
    const SERVICE_RKEEPER = 'Rkws';

    /** SERVICE IIKO name */
    const SERVICE_IIKO = 'Iiko';

    /** SERVICE "id - name" mapping */
    public $allServicesMap = [
        1 => self::SERVICE_RKEEPER,
        2 => self::SERVICE_IIKO,
    ];

    public $syncResult = [];

    /**
     * Construct method for Class SyncServiceFactory
     * @param int $service_id Service ID
     * @param array $params Transaction params
     * @param string $callback_task_id Callback task id
     * @throws BadRequestHttpException
     */
    public function __construct(int $service_id = 0, array $params, string $callback_task_id = null)
    {

        # 1. Load integration script with application environment params
        parent::__construct();
        SyncLog::trace('Loaded integration script with env and post params');

        # 2. Identify Service ID or CALLLBACK
        if (!$callback_task_id) {

            # 2.1.1. Identify Service ID
            if (!array_key_exists($service_id, $this->allServicesMap)) {
                SyncLog::trace('Invalid service_id: "' . $service_id . '"');
                throw new BadRequestHttpException("empty_param|params");
            } else {
                SyncLog::trace('Identified Service ID: ' . $service_id);
            }

            # 2.1.2. Use entity class (by factory)
            $entity = $this->factory((int)$service_id, (string)$this->allServicesMap[$service_id]);
            SyncLog::trace('Initialized entity class: ' . get_class($entity), $this->allServicesMap[$service_id]);

            # 2.1.3. Load dictionary data
            /** AbstractSyncFactory $entity */
            $this->syncResult = $entity->getObjects($params);
            $this->syncResult = $entity->loadDictionary($params);

        } else {

            // сейчас дописываем коллбек
            $this->syncResult = [];
        }

    }

    /**
     * Service Class Factory
     * @param int $serviceId Service ID
     * @param string $serviceName Service name
     * @return AbstractSyncFactory
     * @throws BadRequestHttpException
     */
    public function factory(int $serviceId, string $serviceName): AbstractSyncFactory
    {
        $className = __NAMESPACE__ . '\\sync\\Service' . $serviceName;
        if (class_exists($className)) {
            return new $className($serviceName, $serviceId);
        } else {
            SyncLog::trace("The requested service class does not exist!");
            throw new BadRequestHttpException("class_not_exist");
        }
    }

}