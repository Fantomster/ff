<?php
namespace api_web\modules\integration\classes\documents;

use api_web\modules\integration\classes\DocumentWebApi;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\EdiOrder as BaseOrder;

class EdiOrder extends BaseOrder implements DocumentInterface
{

    /**
     * Порлучение данных из модели
     * @return mixed
     */
    public function prepare()
    {
        if (isset($this->order_id)) {
            return [];
        }

       $return = Order::prepareModel($this->order_id);
       $return["type"] = DocumentWebApi::TYPE_ORDER_EDI;
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