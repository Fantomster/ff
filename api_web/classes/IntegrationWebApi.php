<?php

namespace api_web\classes;

use api\common\models\iiko\iikoWaybill;
use api_web\components\WebApi;
use api_web\modules\integration\interfaces\ServiceInterface;
use common\models\search\OrderSearch;
use yii\base\Exception;
use Yii;

class IntegrationWebApi extends WebApi
{

    private static $service = [
        \api_web\modules\integration\modules\iiko\models\iikoService::class
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

            $result[] = [
                'service' => $service->getServiceName(),
                'image' => \Yii::$app->params['web'] . 'images/' . $service->getServiceName() . '.jpg',
                'license' => $this->prepareLicense($service->getLicenseMixCart()),
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
                "status" => $model->status_id == 2 ? 'Активна' : "Не активна",
                "from" => date('d.m.Y', strtotime($model->fd)),
                "to" => date('d.m.Y', strtotime($model->td)),
                "number" => $model->id
            ];
        }
        return [
            "status" => "Не активна",
            "from" => null,
            "to" => null,
            "number" => null
        ];
    }
}