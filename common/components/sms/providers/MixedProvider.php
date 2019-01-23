<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components\sms\providers;

/**
 * Description of MixedProvider
 *
 * @author El Babuino
 */
class MixedProvider extends Qtelecom
{

    public $sqsQueueUrl;

    /**
     * Отправка сообщения
     *
     * @param string $message
     * @param string $target
     */
    public function send($message, $target)
    {
        $target = is_array($target) ? $target : [$target];
        $smsQueue = [];

        foreach ($target as $recipient) {
            $model = new \common\models\SmsSend([
                'provider'  => get_class($this),
                'target'    => $recipient,
                'text'      => $message,
                'status_id' => \common\models\SmsStatus::STATUS_NEW,
                'order_id'  => $this->order_id,
            ]);
            $model->save();
            $smsQueue[] = $model->id;
        }

        try {
            \Yii::$app->get('sqsQueue')->sendMessage($this->sqsQueueUrl, $smsQueue);
        } catch (\Exception $e) {
            \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
        }
    }

}
