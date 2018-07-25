<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\modules\integration\interfaces\ServiceInterface;
use api_web\modules\integration\modules\one_s\models\one_sService;
use api_web\modules\integration\modules\rkeeper\models\rkeeperService;
use api_web\modules\integration\modules\iiko\models\iikoService;
use yii\base\Exception;

class IntegrationWebApi extends WebApi
{

    private static $service = [
        iikoService::class,
        rkeeperService::class,
        one_sService::class
    ];

    /**
     * Список интеграторов и лицензий
     * @return array
     * @throws Exception
     */
    public function list()
    {
        $result = [];
        foreach (self::$service as $service_class) {
            /**
             * @var $service ServiceInterface
             */
            $service = new $service_class();

            if (!($service instanceof ServiceInterface)) {
                throw new Exception(get_class($service) . ' not implements ServiceInterface');
            }

            $license = $this->prepareLicense($service->getLicenseMixCart());
            $license['status'] = $service->getLicenseMixCartActive() === true ? 'Активна' : "Не активна";

            $result[] = [
                'service' => $service->getServiceName(),
                'image' => \Yii::$app->params['web'] . 'images/' . $service->getServiceName() . '.jpg',
                'license' => $license,
                'options' => $service->getOptions()
            ];


        }
        return ['services' => $result];
    }

    /**
     * Лицензии к выдаче
     * @param $model
     * @return array
     */
    private function prepareLicense($model)
    {
        if (!empty($model)) {
            return [
                "from" => date('d.m.Y', strtotime($model->fd)),
                "to" => date('d.m.Y', strtotime($model->td)),
                "number" => $model->id
            ];
        }
        return [
            "from" => null,
            "to" => null,
            "number" => null
        ];
    }
}