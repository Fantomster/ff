<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\classes;

use common\models\vetis\VetisForeignEnterprise;
use console\modules\daemons\components\MercDictConsumer;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercLogger;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercForeignEnterpriseList extends MercDictConsumer
{
    public static $timeout  = 60*60*24;
    public static $timeoutExecuting = 60*60*12;
    /**
     * Обработка и сохранение результата
     * @param $list
     */
    protected function saveList($list)
    {
        $list = is_array($list) ? $list : [$list];
        $result = [];
        foreach ($list as $item) {
            $model = $this->modelClassName::findOne(['uuid' => $item->uuid]);

            if ($model == null) {
                $model = new $this->modelClassName();
            }
            $attributes = json_decode(json_encode($item), true);
            $model->uuid = $attributes['uuid'];
            $model->guid = $attributes['guid'];
            $model->last = (int)$attributes['last'];
            $model->active = (int)$attributes['active'];
            $model->type = $attributes['type'];
            $model->next = $attributes['next'] ?? null;
            $model->previous = $attributes['previous'] ?? null;
            $model->name = $attributes['name'];
            $model->country_guid = $attributes['address']['country']['guid'];
            $model->addressView = $attributes['address']['addressView'];
            $model->owner_guid = $attributes['owner']['guid'];
            $model->owner_uuid = $attributes['owner']['uuid'];
            $model->data = serialize($item);
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
        $this->instance = cerberApi::getInstance($this->org_id);
        $this->modelClassName = VetisForeignEnterprise::class;
    }
}