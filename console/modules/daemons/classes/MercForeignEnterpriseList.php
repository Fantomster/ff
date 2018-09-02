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
            $model->last = $attributes['last'];
            $model->active = $attributes['active'];
            $model->type = $attributes['type'];
            $model->next = $attributes['next'] ?? null;
            $model->previous = $attributes['previous'] ?? null;
            $model->name = $attributes['name'];
            $model->country_guid = $attributes['address']['country']['guid'];
            $model->addressView = $attributes['address']['addressView'];
            $model->data = serialize($item);
            if (!$model->save()) {
                $result[]['error'] = $model->getErrors();
                $result[]['model-data'] = $model->attributes;
            }
        }

        if(empty($result)) {
            mercLogger::getInstance()->addMercLogDict('COMPLETE', $this->modelClassName, null);
        }
        else{
            mercLogger::getInstance()->addMercLogDict('ERROR', $this->modelClassName, json_encode($result));
        }
    }


    protected function init()
    {
        $this->instance = cerberApi::getInstance($this->org_id);
        $data = json_decode($this->data, true);
        $this->method = $data['method'];
        $this->request = json_decode($data['request'], true);
        $this->listName = $data['struct']['listName'];
        $this->listItemName = $data['struct']['listItemName'];
        $this->modelClassName = VetisForeignEnterprise::class;
    }
}