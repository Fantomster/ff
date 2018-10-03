<?php
namespace api_web\modules\integration\classes\documents;

use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\WaybillContent as BaseWaybillContent;

class WaybillContent extends BaseWaybillContent implements DocumentInterface
{

    /**
     * Порлучение данных из модели
     * @return mixed
     */
    public function prepare()
    {
        if (empty($model)) {
            return [];
        }

        $orderContent = $this->orderContent;
        $productOuter = $this->productOuter;
        $unit = null;
        if(isset($orderContent)) {
            $unit = $orderContent->product->unit;
        }
        elseif (isset($productOuter)) {
            $unit = isset($productOuter->outerUnit) ? $productOuter->outerUnit->name : null;
        }

        $return = [
            "id" => $this->id,
            "product_id" => isset($orderContent) ? $orderContent->product_id : null,
            "product_name" => isset($orderContent) ? $orderContent->product->product : null,
            "product_outer_id" => isset($this->productOuter) ? $productOuter->name : null,
            "quantity" => $this->quantity_waybill,
            "unit" => $unit,
            "koef" => $this->koef,
            "sum_without_vat" => $this->sum_without_vat,
            "sum_with_vat" => $this->sum_with_vat,
            "vat" => $this->vat_waybill,
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