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
     */
    public function __construct(int $service_id = 0, array $params, string $callback_task_id = null)
    {
        # 1. Load integration script with application environment params
        parent::__construct();
        SyncLog::fix('Loaded integration script with env and post params');

        # 2. Identify Service ID or CALLLBACK
        if (!$callback_task_id) {

            # 2.1. Identify Service ID
            if (!array_key_exists($service_id, $this->allServicesMap)) {
                SyncLog::exit('Invalid service_id: "' . $service_id . '"');
            }
            SyncLog::fix('Identified Service ID: ' . $service_id);

            # 2.2. Use entity class (by factory)
            $entity = $this->factory($this->allServicesMap[$service_id]);
            SyncLog::fix('Initialized entity class: ' . get_class($entity));

            # 2.3. Load dictionary data
            /** AbstractSyncFactory $entity */
            $this->syncResult = $entity->loadDictionary($params);

        } else {

            // сейчас дописываем коллбек
            $this->syncResult = [];
        }

    }

    /**
     * Service Class Factory
     * @param string $service Service name
     * @return AbstractSyncFactory?
     */
    public function factory(string $service): ?AbstractSyncFactory
    {
        $className = __NAMESPACE__ . '\\sync\\Service' . $service;
        if (class_exists($className)) {
            return new $className($service);
        }
        SyncLog::exit("The requested dictionary class does not exist!", "class_not_exist");
        return null;
    }

}