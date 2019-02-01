<?php

namespace common\components\mailer;

use common\models\notifications\EmailBlacklist;
use common\models\EmailQueue;

/**
 * Description of Mailer
 *
 * @author elbabuino
 */
class Mailer extends \yii\mail\BaseMailer
{

    private $to;
    private $subject;
    public $sqsQueueUrl;
    public $defaultFrom = "";
    public $messageClass = 'common\components\mailer\Message';
    
    public function compose($view = null, array $params = [])
    {
        $message = $this->createMessage();
        if ($view === null) {
            return $message;
        }

        if (array_key_exists('order', $params)) {
            $message->setOrderId(isset($params['order']->id) ? $params['order']->id : null);
        }

        if (is_array($view)) {
            if (isset($view['html'])) {
                $html = $this->render($view['html'], $params, $this->htmlLayout);
            }
            if (isset($view['text'])) {
                $text = $this->render($view['text'], $params, $this->textLayout);
            }
        } else {
            $html = $this->render($view, $params, $this->htmlLayout);
        }

        if (isset($html)) {
            $message->setHtmlBody($html);
        } elseif (isset($text)) {
            $message->setHtmlBody($text);//html only
        }

        return $message;
    }

//    public function send($message = null)
//    {
//        $newEmail = new EmailQueue();
//        $newEmail->to = $this->to;
//        $newEmail->from = $this->defaultFrom;
//        $newEmail->subject = $this->subject;
//        $newEmail->body = $this->html;
//        $newEmail->order_id = $this->order_id;
//        $newEmail->status = EmailQueue::STATUS_NEW;
//
//        if (substr($this->to, -4) === "_bak") {
//            $newEmail->status = EmailQueue::STATUS_FAILED;
//        }
//        //check blacklist
//        if (EmailBlacklist::find()->where(['email' => $this->to])->exists()) {
//            $newEmail->status = EmailQueue::STATUS_FAILED;
//        }
//
//        $save = $newEmail->save();
//        if ($save && !($newEmail->status == EmailQueue::STATUS_FAILED)) {
//            try {
//                $result = \Yii::$app->get('sqsQueue')->sendMessage($this->sqsQueueUrl, [$newEmail->id]);
//            } catch (\Exception $e) {
//                \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
//            }
//        }
//
//        return $save;
//    }

    /**
     * @inheritdoc
     */
    protected function sendMessage($message)
    {
        if ( is_null($message->getFrom()) && isset($this->defaultFrom)) {
            if(!is_array($this->defaultFrom)){
                $this->defaultFrom = array($this->defaultFrom => $this->defaultFrom);
            }
            $message->setFrom($this->defaultFrom);
        }
        
        $newEmail = new EmailQueue();
        $newEmail->to = $message->getTo();
        $newEmail->from = $message->getFrom();
        $newEmail->subject = $message->getSubject();
        $newEmail->body = $message->getHtmlBody();
        $newEmail->order_id = $message->getOrderId();
        $newEmail->status = EmailQueue::STATUS_NEW;

        if (substr($this->to, -4) === "_bak") {
            $newEmail->status = EmailQueue::STATUS_FAILED;
        }
        //check blacklist
        if (EmailBlacklist::find()->where(['email' => $newEmail->to])->exists()) {
            $newEmail->status = EmailQueue::STATUS_FAILED;
        }

        $save = $newEmail->save();
        $result = false;
        
        if ($save && !($newEmail->status == EmailQueue::STATUS_FAILED)) {
            try {
                $result = \Yii::$app->get('sqsQueue')->sendMessage($this->sqsQueueUrl, [$newEmail->id]);
            } catch (\Exception $e) {
                \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
            }
        }

        return $save && $result;
    }

}
