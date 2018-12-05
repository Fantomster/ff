<?php

namespace api_web\modules\integration\classes\documents;

use api_web\helpers\CurrencyHelper;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\WaybillContent as BaseWaybillContent;

class WaybillContent extends BaseWaybillContent implements DocumentInterface
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

        $orderContent = $this->orderContent;

        $return = [
            "id"              => $this->id,
            "product_id"      => isset($orderContent) ? $orderContent->product_id : null,
            "product_name"    => isset($orderContent) ? $orderContent->product->product : null,
            "mixcart_count"   => isset($orderContent) ? $orderContent->quantity : null,
            "mixcart_unit"    => isset($orderContent) ? $orderContent->product->ed : null,
            "quantity"        => $this->quantity_waybill,
            "outer_product"   => $this->getOuterProduct(),
            "outer_unit"      => $this->getOuterUnitObject(),
            "koef"            => $this->koef,
            "merc_uuid"       => isset($orderContent) ? $orderContent->merc_uuid : null,
            "sum_without_vat" => CurrencyHelper::asDecimal($this->sum_without_vat),
            "sum_with_vat"    => CurrencyHelper::asDecimal($this->sum_with_vat),
            "vat"             => $this->vat_waybill
        ];

        return $return;
    }

    /**
     * Информация о внешнем продукте
     *
     * @return array|null
     */
    private function getOuterProduct()
    {
        if (empty($this->productOuter)) {
            return null;
        }

        return [
            'id'   => $this->productOuter->id,
            'name' => $this->productOuter->name
        ];
    }

    /**
     * Информация о внешних еденицах измерения
     *
     * @return array|null
     */
    private function getOuterUnitObject()
    {
        $productOuter = $this->productOuter;
        if (empty($productOuter->outerUnit)) {
            return null;
        }

        return [
            'id'   => $productOuter->outerUnit->id,
            'name' => $productOuter->outerUnit->name
        ];
    }

    /**
     * Загрузка модели и получение данных
     *
     * @param $key
     * @return array|mixed $array
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