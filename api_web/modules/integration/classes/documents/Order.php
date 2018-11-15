<?php

namespace api_web\modules\integration\classes\documents;

use api_web\classes\DocumentWebApi;
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
            if (is_null(reset($arWaybillNames))) {
                $arWaybillNames = null;
            }
        }

        $return = [
            "id"              => (int)$this->id,
            "number"          => $arWaybillNames ?? [],
            "type"            => DocumentWebApi::TYPE_ORDER,
            "status_id"       => (int)$this->status,
            "status_text"     => $this->statusText,
            "service_id"      => (int)$this->service_id,
            "is_mercury_cert" => $this->getIsMercuryCert(),
            "count"           => (int)$this->positionCount,
            "total_price"     => CurrencyHelper::asDecimal($this->total_price),
            "doc_date"        => date("Y-m-d H:i:s T", strtotime($this->created_at)),
            "vendor"          => null,
            "agent"           => null,
            "store"           => null
        ];

        $vendor = $this->vendor;
        $return["vendor"] = [
            "id"    => (int)$vendor->id,
            "name"  => $vendor->name,
            "difer" => false,
        ];

        $agent = OuterAgent::findOne(['vendor_id' => $vendor->id, 'org_id' => $this->client_id]);
        if (!empty($agent)) {
            $return["agent"] = [
                'id'   => (int)$agent->id,
                'name' => $agent->name,
            ];
        }

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
     * @param $serviceId
     * @return array
     */
    public static function prepareModel($key, $serviceId = null)
    {
        $where = ['id' => $key];
        if (!is_null($serviceId)){
            $where['service_id'] = $serviceId;
        }
        $model = self::findOne($where);
        if ($model === null) {
            return [];
        }
        return $model->prepare();
    }
}