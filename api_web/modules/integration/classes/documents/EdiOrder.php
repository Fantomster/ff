<?php

namespace api_web\modules\integration\classes\documents;

use api_web\classes\DocumentWebApi;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\EdiOrder as BaseOrder;

class EdiOrder extends BaseOrder implements DocumentInterface
{

    public static $waybill_service_id = null;

    /**
     * Порлучение данных из модели
     *
     * @return mixed
     * @throws \yii\db\Exception
     */
    public function prepare()
    {
        if (isset($this->order_id)) {
            return [];
        }

        $return = Order::prepareModel($this->order_id, self::$waybill_service_id);
        $return["type"] = DocumentWebApi::TYPE_ORDER_EDI;
        return $return;
    }

    /**
     * Загрузка модели и получение данных
     *
     * @param      $key
     * @param null $serviceId
     * @return array|mixed
     */
    public static function prepareModel($key, $serviceId = null)
    {
        if ($serviceId) {
            self::$waybill_service_id = $serviceId;
        }

        $model = self::findOne(['id' => $key]);
        if ($model === null) {
            return [];
        }
        return $model->prepare();
    }
}