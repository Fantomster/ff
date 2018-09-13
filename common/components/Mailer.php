<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

use common\models\notifications\EmailBlacklist;
use common\models\EmailQueue;

/**
 * Description of Mailer
 *
 * @author elbabuino
 */
class Mailer extends \yii\mail\BaseMailer
{
    private $order_id;
    private $html;
    private $to;
    private $subject;
    public $defaultFrom = "";
    public $queueName = "process_email";
    
    public function compose($view = null, array $params = [])
    {
        if (array_key_exists('order', $params)) {
            $this->order_id = isset($params['order']->id) ? $params['order']->id : null;
        }
        
        if (is_array($view)) {
            if (isset($view['html'])) {
                $html = $this->render($view['html'], $params, $this->htmlLayout);
            }
        } else {
            $html = $this->render($view, $params, $this->htmlLayout);
        }

        $this->html = $html;

        return $this;
    }

    public function setTo($to) {
        $this->to = $to;
        return $this;
    }
    
    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }
    
    public function send($message = null)
    {
        $newEmail = new EmailQueue();
        $newEmail->to = $this->to;
        $newEmail->from = $this->defaultFrom;
        $newEmail->subject = $this->subject;
        $newEmail->body = $this->html;
        $newEmail->order_id = $this->order_id;
        $newEmail->status = EmailQueue::STATUS_NEW;

        if (substr($this->to, -4) === "_bak") {
            $newEmail->status = EmailQueue::STATUS_FAILED;
        }
        //check blacklist
        if (EmailBlacklist::find()->where(['email' => $this->to])->exists()) {
            $newEmail->status = EmailQueue::STATUS_FAILED;
        }

        
        if ($newEmail->save() && !($newEmail->status == EmailQueue::STATUS_FAILED)) {
            try {
            \Yii::$app->get('rabbit')
                ->setQueue($this->queueName)
                ->addRabbitQueue(json_encode([$newEmail->id]));
            } catch (\Exception $e) {
                Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
            }
        }

        return $newEmail->save();
    }


    /**
     * @inheritdoc
     */
    protected function sendMessage($message = null)
    {
        //stub
    }

}
