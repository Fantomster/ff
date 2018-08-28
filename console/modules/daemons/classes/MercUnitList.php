<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\classes;

use api\common\models\RabbitQueues;
use common\models\vetis\VetisUnit;
use console\modules\daemons\components\MercDictConsumer;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercUnitList extends MercDictConsumer
{
    protected function saveList($list) {
        $list = is_array($list) ? $list : [$list];
        foreach ($list as $item)
        {
            $model = VetisUnit::findOne(['guid' => $item->guid]);

            if($model == null)
                $model = new VetisUnit();
            $attributes =  json_decode(json_encode($item), true);
            $model->setAttributes($attributes);
            $model->createDate = date('Y-m-d H:i:s',strtotime($model->createDate));
            $model->updateDate = date('Y-m-d H:i:s',strtotime($model->updateDate));
            if (!$model->save())
                $this->result = false;
        }
    }

    protected function init()
    {
        $this->instance = dictsApi::getInstance(5144);
        $this->instance->setMode(dictsApi::GET_UPDATES_DICTS);
        $this->method = (RabbitQueues::findOne(['consumer_class_name' => get_class($this)]) === null) ? 'getUnitList' : 'getUnitChangesList';
        $this->listName = 'unitList';
        $this->listItemName = 'unit';
    }
}