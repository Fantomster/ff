<?php

namespace api_web\modules\integration\classes\documents;

use api_web\classes\DocumentWebApi;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\IntegrationInvoice as BaseOrder;
use common\models\OuterAgent;

class OrderEmail extends BaseOrder implements DocumentInterface
{

    /**
     * Порлучение данных из модели
     *
     * @return mixed
     */
    public function prepare()
    {
        if (empty($this->attributes)) {
            return [];
        }

        $order = (isset($this->order_id)) ? $this->order() : null;

        $return = [
            "id"          => $this->id,
            "number"      => $this->number,
            "type"        => DocumentWebApi::TYPE_ORDER_EMAIL,
            "status_id"   => isset($order) ? $order->status_id : null,
            "status_text" => isset($order) ? $order->statusText : null,
            "service_id"  => isset($order) ? $order->service_id : null
        ];

        $return ["agent"] = null;

        $vendor = null;
        if (isset($this->vendor_id)) {
            $vendor = $this->vendor;
        } elseif (isset($order)) {
            $vendor = $order->vendor;
        }

        if ($vendor != null) {
            $return["vendor"] = [
                "id"    => $vendor->id,
                "name"  => $vendor->name,
                "difer" => false,
            ];
            $agent = OuterAgent::findOne(['vendor_id' => $vendor->id]);
            $return ["agent"] = [
                'name' => $agent->name,
                'id' => $agent->id,
            ];
        } else {
            $return["vendor"] = null;
        }
        $return["is_mercury_cert"] = isset($order) ? $order->getIsMercuryCert() : null;
        $return["count"] = (int)count($this->content);
        $return["total_price"] = (float)$this->totalSumm;
        $return["doc_date"] = date("Y-m-d H:i:s T", strtotime($this->date));

        return $return;
    }

    /**
     * Загрузка модели и получение данных
     *
     * @param $key
     * @return array
     */
    public static function prepareModel($key)
    {
        $model = self::findOne(['id' => $key]);
        if ($model === null) {
            return [];
        }
        return $model->prepare();
    }
}