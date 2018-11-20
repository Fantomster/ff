<?php

namespace api_web\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class WaybillBehavior extends Behavior
{
    /** @var \common\models\Waybill $model */
    public $model;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate'
        ];
    }

    /**
     * Смена статуса накладной в "сопоставленно"
     *
     * @param $event
     * @return bool
     */
    public function afterUpdate($event)
    {
        return $this->model->changeStatusToCompared();
    }
}