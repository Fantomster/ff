<?php

namespace api_web\components\notice_class;

use Yii;
use yii\helpers\Json;
use common\models\Order;
use yii\data\ArrayDataProvider;
use common\models\Organization;

class OrderNotice
{
    /**
     * @param $vendor Organization
     * @return bool
     */
    public function sendOrderToTurnVendor($vendor)
    {
        $vendorUsers = $vendor->users;
        foreach ($vendorUsers as $user) {
            $channel = 'user' . $user->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode(['channel' => $channel, 'isSystem' => 3])
            ]);
        }
        return true;
    }

    /**
     * @param $client Organization
     * @return bool
     */
    public function sendOrderToTurnClient($client)
    {
        $clientUsers = $client->users;
        foreach ($clientUsers as $user) {
            $channel = 'user' . $user->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode(['body' => $client->getCartCount(), 'channel' => $channel, 'isSystem' => 2])
            ]);
        }
        return true;
    }

    /**
     * Отправка Email и СМС при создании заказа
     * @param $sender Organization
     * @param $order Order
     */
    public function sendEmailAndSmsOrderCreated($sender, $order)
    {
        /** @var \yii\swiftmailer\Mailer $mailer */
        /** @var \yii\swiftmailer\Message $message */
        $mailer = Yii::$app->mailer;
        $senderOrg = $sender;
        $subject = Yii::t('message', 'frontend.controllers.order.new_order') . $order->id . "!";
        $dataProvider = new ArrayDataProvider(['allModels' => $order->orderContent, 'pagination' => false]);
        $order->recipientsList;
        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            $notification = ($recipient->getEmailNotification($order->vendor_id)) ? $recipient->getEmailNotification($order->vendor_id) : $recipient->getEmailNotification($order->client_id);
            if ($notification)
                if($notification->order_created)
                {
                $mailer->compose('orderCreated', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                    ->setTo($email)
                    ->setSubject($subject)
                    ->send();
            }
            $notification = ($recipient->getSmsNotification($order->vendor_id)) ? $recipient->getSmsNotification($order->vendor_id) : $recipient->getSmsNotification($order->client_id);
            if ($notification)
                if($recipient->profile->phone && $notification->order_created)
                {
                $text = Yii::$app->sms->prepareText('sms.order_new', [
                    'name' => $senderOrg->name,
                    'url' => $order->getUrlForUser($recipient)
                ]);
                Yii::$app->sms->send($text, $recipient->profile->phone);
            }
        }
    }
}