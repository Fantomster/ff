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

        $order = (isset($this->order_id)) ? $this->order : null;

        $return = [
            "id"                => (int)$this->id,
            "number"            => $this->number ? [$this->number] : null,
            "type"              => DocumentWebApi::TYPE_ORDER_EMAIL,
            "status_id"         => isset($order) ? (int)$order->status : null,
            "status_text"       => isset($order) ? $order->statusText : null,
            "service_id"        => isset($order) ? (int)$order->service_id : null,
            "replaced_order_id" => isset($order) ? (int)$order->replaced_order_id : null
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
                "id"    => (int)$vendor->id,
                "name"  => $vendor->name,
                "difer" => false,
            ];
            $agent = OuterAgent::findOne(['vendor_id' => $vendor->id]);
            $return ["agent"] = !empty($agent) ? [
                'name' => $agent->name,
                'id'   => (int)$agent->id,
            ] : null;
        } else {
            $return["vendor"] = null;
        }
        $return["is_mercury_cert"] = false;
        $return["count"] = (int)count($this->content);
        $return["total_price"] = $this->totalSumm;
        $return["doc_date"] = date("Y-m-d H:i:s T", strtotime($this->date));
        $return["store"] = null; //todo_refactoring

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