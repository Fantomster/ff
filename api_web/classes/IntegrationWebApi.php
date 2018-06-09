<?php

namespace api_web\classes;

use api\common\models\iiko\iikoService;
use api\common\models\iiko\iikoWaybill;
use api\common\models\RkService;
use api\common\models\RkServicedata;
use api\common\models\RkWaybill;
use api_web\components\WebApi;
use common\models\Order;

class IntegrationWebApi extends WebApi
{

    private static $service = [
        'iiko' => \api\common\models\iiko\iikoService::class,
        'mercuriy' => \api\common\models\merc\mercService::class,
        'r-keeper' => \api\common\models\RkService::class
    ];

    /**
     * Список интеграторов и лицензий
     * @return array
     */
    public function list()
    {
        $result = [];
        foreach (self::$service as $name => $service_class) {
            $result[] = [
                'service' => $name,
                'image' => \Yii::$app->params['web'] . 'images/' . $name . '.jpg',
                'not_formed' => $this->notFormedWaybillCount($service_class),
                'awaiting' => $this->waitingWaybillCount($service_class),
                'license' => $this->getLicenseService($service_class)
            ];
        }
        return ['providers' => $result];
    }

    /**
     * Сколько накладных ожидают выгрузки во внешний сервис
     * @param $class
     * @return int
     */
    private function waitingWaybillCount($class)
    {
        $result = 0;

        if ($class == iikoService::class) {
            $result = iikoWaybill::find()->where(['org' => $this->user->organization->id, 'status_id' => 1])->count();
        }

        if ($class == RkService::class) {
            $result = RkWaybill::find()->where(['org' => $this->user->organization->id, 'status_id' => 1])->count();
        }

        return (int)$result;
    }

    /**
     * Сколько есть выполненых заказов, но наклыдные не формировались
     * @param $class
     * @return int
     */
    private function notFormedWaybillCount($class)
    {
        $result = 0;

        if ($class == iikoService::class) {
            $orders = Order::find()->where(['status' => Order::STATUS_DONE, 'client_id' => $this->user->organization->id])->all();
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    if (!iikoWaybill::find()->where(['order_id' => $order->id])->exists()) {
                        $result++;
                    }
                }
            }
        }

        if ($class == RkService::class) {
            $orders = Order::find()->where(['status' => Order::STATUS_DONE, 'client_id' => $this->user->organization->id])->all();
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    if (!RkWaybill::find()->where(['order_id' => $order->id])->exists()) {
                        $result++;
                    }
                }
            }
        }

        return (int)$result;
    }

    /**
     * Лицензии на сервис
     * @param $service_class
     * @return array
     */
    private function getLicenseService($service_class)
    {
        $result = [];

        $model = ($service_class)::find(['org' => $this->user->organization->id])->orderBy('fd DESC')->one();
        $result['mixcart'] = $this->prepareLicense($model);

        if ($service_class == RkService::class) {
            $model = RkServicedata::find(['org' => $this->user->organization->id])->orderBy('fd DESC')->one();
            $result['r-keeper'] = $this->prepareLicense($model);
        }

        return $result;
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