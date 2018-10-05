<?php
namespace api_web\modules\integration\classes\documents;

use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\IntegrationInvoiceContent as BaseOrderContent;

class OrderContentEmail extends BaseOrderContent implements DocumentInterface
{

    /**
     * Порлучение данных из модели
     * @return mixed
     */
    public function prepare()
    {
        if (empty($this->attributes)) {
            return [];
        }

        $return = [
            "id" => $this->id,
            "product_id" => null,
            "product_name" => $this->title,
            "quantity" => $this->quantity,
            "unit" => $this->ed,
            "price" => $this->price_nds,
            "is_fullmap" => false,

        ];

        return $return;
    }

    /**
     * Загрузка модели и получение данных
     * @param $key
     * @return $array
     */
    public static function prepareModel($key)
    {
        $model = self::findOne(['id' => $key]);
        if($model === null ) {
            return [];
        }
        return $model->prepare();
    }
}