<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\classes;
use common\models\vetis\VetisProductItem;
use console\modules\daemons\components\MercDictConsumer;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercLogger;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercProductItemList extends MercDictConsumer
{
    public static $timeout  = 60*60*24;
    public static $timeoutExecuting = 60*60*12;

    public static function updateList($list)
    {
        $obj = new self(0);
        $obj->init();
        $obj->saveList($list);
    }

    /**
     * Обработка и сохранение результата
     * @param $list
     */
    protected function saveList($list)
    {
        $list = is_array($list) ? $list : [$list];
        $result = [];
        foreach ($list as $item)
        {
            $model = $this->modelClassName::findOne(['uuid' => $item->uuid]);

            if($model == null) {
                $model = new $this->modelClassName();
            }
            $attributes =  json_decode(json_encode($item), true);
            $model->setAttributes($attributes, false);
            $model->last = (int)$attributes['last'];
            $model->active = (int)$attributes['active'];
            $model->correspondsToGost = (int)$attributes['correspondsToGost'];
            $model->product_guid = $attributes['product']['guid'];
            $model->product_uuid = $attributes['product']['uuid'];
            $model->subproduct_guid = $attributes['subProduct']['guid'];
            $model->subproduct_uuid = $attributes['subProduct']['uuid'];
            $model->producer_guid = $attributes['producer']['guid'];
            $model->producer_uuid = $attributes['producer']['uuid'];
            $model->tmOwner_guid = $attributes['tmOwner']['guid'];
            $model->tmOwner_uuid = $attributes['tmOwner']['uuid'];
            $model->data = serialize($item);
            $model->createDate = date('Y-m-d H:i:s',strtotime($model->createDate));
            $model->updateDate = date('Y-m-d H:i:s',strtotime($model->updateDate));
            if (isset($attributes['packaging'])) {
                $model->packagingType_guid = isset($attributes['packaging']['packagingType']['guid']) ? $attributes['packaging']['packagingType']['guid'] : null;
                $model->packagingType_uuid = isset($attributes['packaging']['packagingType']['uuid']) ? $attributes['packaging']['packagingType']['uuid'] : null;
                $model->unit_uuid = isset($attributes['packaging']['unit']['uuid']) ? $attributes['packaging']['unit']['uuid'] : null;
                $model->unit_guid = isset($attributes['packaging']['unit']['guid']) ? $attributes['packaging']['unit']['guid'] : null;
                $model->packagingQuantity = isset($attributes['packaging']['quantity']) ? $attributes['packaging']['quantity'] : null;
                $model->packagingVolume = isset($attributes['packaging']['volumne']) ? $attributes['packaging']['volumne'] : null;
            }
            if (!$model->save()) {
                $result[]['error'] = $model->getErrors();
                $result[]['model-data'] = $model->attributes;
            }
        }
        return $result;
    }

    protected function init()
    {
        parent::init();
        $this->instance = productApi::getInstance($this->org_id);
        $this->modelClassName = VetisProductItem::class;
    }
}