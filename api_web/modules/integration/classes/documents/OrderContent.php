<?php

namespace api_web\modules\integration\classes\documents;

use api\common\models\AllMaps;
use api_web\modules\integration\interfaces\DocumentInterface;
use api_web\modules\integration\modules\iiko\models\iikoService;
use common\models\OrderContent as BaseOrderContent;

class OrderContent extends BaseOrderContent implements DocumentInterface
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

        $return = [
            "id"            => $this->id,
            "product_id"    => $this->product_id,
            "edi_number"    => $this->edi_number,
            "product_name"  => $this->product->product,
            "quantity"      => $this->quantity,
            "unit"          => $this->product->ed,
            "price"         => $this->price,
            "merc_uuid"     => $this->merc_uuid ?? null
        ];

        return $return;
    }

    /**
     * Загрузка модели и получение данных
     *
     * @param $key
     * @return $array
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