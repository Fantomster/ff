<?php
namespace api_web\modules\integration\classes\documents;

use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\EdiOrderContent as BaseOrderContent;

class EdiOrderContent extends BaseOrderContent implements DocumentInterface
{

    /**
     * Порлучение данных из модели
     * @return mixed
     */
    public function prepare()
    {
        if (empty($this->order_content_id)) {
            return [];
        }

        return OrderContent::prepareModel($this->order_content_id);
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
        if($model === null ) {
            return [];
        }
        return $model->prepare();
    }
}