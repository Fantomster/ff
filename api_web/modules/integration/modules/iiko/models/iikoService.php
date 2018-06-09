<?php

namespace api_web\modules\integration\modules\iiko\models;

use api\common\models\iiko\iikoWaybill;
use api_web\components\WebApi;
use api_web\modules\integration\interfaces\ServiceInterface;
use common\models\Order;

class iikoService extends WebApi implements ServiceInterface
{

    public function getLicenseMixCart()
    {
        return \api\common\models\iiko\iikoService::find(['org' => $this->user->organization->id])->orderBy('fd DESC')->one();
    }

    public function getSettings()
    {
        // TODO: Implement getSettings() method.
    }

    public function getOptions()
    {
        $result = 0;
        $orders = Order::find()->where(['status' => Order::STATUS_DONE, 'client_id' => $this->user->organization->id])->all();
        if (!empty($orders)) {
            foreach ($orders as $order) {
                if (!iikoWaybill::find()->where(['order_id' => $order->id])->exists()) {
                    $result++;
                }
            }
        }

        return [
            'waiting' => (int) iikoWaybill::find()->where(['org' => $this->user->organization->id, 'status_id' => 1])->count(),
            'not_formed' => $result
        ];
    }
}