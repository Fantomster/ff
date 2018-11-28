<?php

namespace api_web\behaviors;

use api_web\classes\DocumentWebApi;
use api_web\components\FireBase;
use api_web\components\Registry;
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
        $this->model->changeStatusToCompared();
        $this->sendNoticeFcm();
        return true;
    }

    /**
     * Отправка информации о накладной в FCM
     */
    private function sendNoticeFcm()
    {
        if (in_array($this->model->status_id, [Registry::WAYBILL_UNLOADED, Registry::WAYBILL_ERROR])) {
            FireBase::getInstance()->update([
                'organization' => $this->model->acquirer_id
            ], [
                'document_refresh' => [
                    'type'        => $this->model->order ? DocumentWebApi::TYPE_ORDER : DocumentWebApi::TYPE_WAYBILL,
                    'document_id' => $this->model->order ? $this->model->order->id : $this->model->id,
                    'service_id'  => $this->model->service_id
                ]
            ]);
        }
    }
}