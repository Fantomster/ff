<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\modules\integration\interfaces\ServiceInterface;

class IntegrationWebApi extends WebApi
{

    private static $service = [
        'iiko' => \api_web\modules\integration\modules\iiko\models\iikoService::class
    ];

    /**
     * Список интеграторов и лицензий
     * @return array
     */
    public function list()
    {
        $result = [];
        foreach (self::$service as $name => $service_class) {
            /**
             * @var $service ServiceInterface
             */
            $service = new $service_class();
            $result[] = [
                'service' => $name,
                'image' => \Yii::$app->params['web'] . 'images/' . $name . '.jpg',
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