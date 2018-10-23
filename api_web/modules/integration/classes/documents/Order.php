<?php

namespace api_web\modules\integration\classes\documents;

use api_web\classes\DocumentWebApi;
use api_web\components\Registry;
use api_web\helpers\CurrencyHelper;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\Order as BaseOrder;
use common\models\OrderContent;
use common\models\OuterAgent;

class Order extends BaseOrder implements DocumentInterface
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

        if (!empty($this->orderContent)) {
            $arWaybillNames = array_values(array_unique(array_map(function (OrderContent $el) {
                return $el->edi_number;
            }, $this->orderContent)));
            if (is_null(reset($arWaybillNames))){
                $arWaybillNames = null;
            }
        }

        $return = [
            "id"          => $this->id,
            "number"      => $arWaybillNames ?? null,
            "type"        => DocumentWebApi::TYPE_ORDER,
            "status_id"   => $this->status,
            "status_text" => $this->statusText,
            "service_id"  => $this->service_id,
        ];

        $vendor = $this->vendor;

        $return["vendor"] = [
            "id"    => $vendor->id,
            "name"  => $vendor->name,
            "difer" => false,
        ];

        $agent = OuterAgent::findOne(['vendor_id' => $vendor->id]);
        $return ["agent"] = !empty($agent) ? [
            'name' => $agent->name,
            'id'   => $agent->id,
        ] : null;

        $return["is_mercury_cert"] = $this->getIsMercuryCert();
        $return["count"] = (int)$this->positionCount;
        $return["total_price"] = CurrencyHelper::asDecimal($this->total_price);
        $return["doc_date"] = date("Y-m-d H:i:s T", strtotime($this->created_at));
        $return["store"] = null; //todo_refactoring

        return $return;
    }

    /**
     * @return bool
     */
    public function getIsMercuryCert()
    {
        return (OrderContent::find()->where(['order_id' => $this->id])->andWhere('merc_uuid is not null')->count()) > 0;
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