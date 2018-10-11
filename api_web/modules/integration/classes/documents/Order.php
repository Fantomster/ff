<?php
namespace api_web\modules\integration\classes\documents;

use api_web\classes\DocumentWebApi;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\Order as BaseOrder;
use common\models\OrderContent;

class Order extends BaseOrder implements DocumentInterface
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
            "mumber" => $this->id,
            "type" => DocumentWebApi::TYPE_ORDER,
            "status_id" => $this->status,
            "status_text" => $this->statusText,
        ];

        $return ["agent"] = [
        ];

        $vendor = $this->vendor;

        $return["vendor"] = [
            "id" => $vendor->id,
            "name" => $vendor->name,
            "difer" => false,
        ];
        $return["is_mercury_cert"] = $this->getIsMercuryCert();
        $return["count"] = $this->positionCount;
        $return["total_price"] = $this->total_price;
        $return["doc_date"] = date("Y-m-d H:i:s T", strtotime($this->created_at));

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