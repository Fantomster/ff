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
     * @return mixed
     */
    public function prepare()
    {
        if (empty($this->attributes)) {
            return [];
        }

        $return = [
            "id" => $this->id,
            "product_id" => $this->product_id,
            "product_name" => $this->product->product,
            "quantity" => $this->quantity,
            "unit" => $this->product->ed,
            "price" => $this->price,
            "is_fullmap" => $this->isFullmap(),

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

    /**
     * Признак наличия позиции в массовом сопоставлении
     * @return bool
     */
    private function isFullmap()
    {
        $client_id = $this->order->client_id;
        $mainOrg = iikoService::getMainOrg($client_id);
        return (AllMaps::find()->where("org_id in ($client_id, $mainOrg) and product_id = $this->product_id and is_active = 1")->one()) != null;

    }
}