<?php

namespace api_web\components\notice_class;

use api_web\components\FireBase;
use common\models\Message;
use common\models\notifications\EmailNotification;
use common\models\notifications\SmsNotification;
use common\models\OrderChat;
use common\models\OrderContent;
use common\models\search\OrderContentSearch;
use common\models\User;
use Yii;
use yii\helpers\Json;
use common\models\Order;
use yii\data\ArrayDataProvider;
use common\models\Organization;
use yii\swiftmailer\Mailer;

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
     * @param $user User
     * @return bool
     */
    public function sendOrderToTurnClient(User $user)
    {
        /**
         * @var $client Organization
         */
        $client = $user->organization;
        $clientUsers = $client->users;
        $count = (int)$client->getCartCount();
        foreach ($clientUsers as $user) {
            $channel = 'user' . $user->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode(['body' => $client->getCartCount(), 'channel' => $channel, 'isSystem' => 2])
            ]);

            FireBase::getInstance()->update([
                'user'         => $user->id,
                'organization' => $client->id
            ], [
                'cart_count'              => $count,
                'last_add_cart_user_name' => $user->profile->full_name
            ]);
        }
        return true;
    }

    /**
     * @param User $userSend
     * @return bool
     */
    public function sendLastUserCartAdd(User $userSend)
    {
        $client = $userSend->organization;
        $clientUsers = $client->users;
        foreach ($clientUsers as $user) {
            FireBase::getInstance()->update([
                'user'         => $user->id,
                'organization' => $client->id
            ], [
                'last_add_cart_user_name' => $userSend->profile->full_name
            ]);
        }
        return true;
    }

    /**
     * Отправка Email и СМС при создании заказа
     *
     * @param $sender Organization
     * @param $order  Order
     */
    public function sendEmailAndSmsOrderCreated($sender, $order)
    {
        /** @var \yii\swiftmailer\Mailer $mailer */
        /** @var \yii\swiftmailer\Message $message */
        $mailer = Yii::$app->mailer;
        $mailer->htmlLayout = '@api_web/views/mail/layouts/order';
        $senderOrg = $sender;
        $subject = Yii::t('message', 'frontend.controllers.order.new_order') . $order->id . "!";
        $dataProvider = new ArrayDataProvider(['allModels' => $order->orderContent, 'pagination' => false]);
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification && $notification->order_created) {

//                    if ($recipient->organization->type_id == Organization::TYPE_RESTAURANT) {
//                        //
//                    }
                    try {
                        $mailer->compose('@api_web/views/mail/orderCreated', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    } catch (\Exception $e) {
                        \Yii::error($e->getMessage());
                    }
                }
                $notification = $recipient->getSmsNotification($org);
                if ($notification && !empty($recipient->profile->phone) && $notification->order_created) {
                    try {
                        $text = Yii::$app->sms->prepareText('sms.order_new', [
                            'name' => $senderOrg->name,
                            'url'  => $order->getUrlForUser($recipient)
                        ]);
                        Yii::$app->sms->send($text, $recipient->profile->phone);
                    } catch (\Exception $e) {
                        \Yii::error($e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Отмена заказа
     *
     * @param User         $user
     * @param Organization $organization
     * @param Order        $order
     */
    public function cancelOrder(User $user, Organization $organization, Order $order)
    {
        $senderOrg = $organization;

        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        $mailer->htmlLayout = '@api_web/views/mail/layouts/order';
        $subject = Yii::t('message', 'frontend.controllers.order.cancelled_order_six', ['ru' => "Заказ № {order_id} отменен!", 'order_id' => $order->id]);

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;

        /**
         * @var $notification EmailNotification|SmsNotification
         */
        foreach ($order->recipientsList as $recipient) {
            //Отправляем Email об отмене заказа
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification) {
                    if ($notification->order_canceled) {
                        $mailer->compose('@api_web/views/mail/orderCanceled', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }
                }
                //Отправляем СМС
                $notification = $recipient->getSmsNotification($org);
                if ($notification) {
                    if (!empty($recipient->profile->phone) && $notification->order_canceled) {
                        $text = Yii::$app->sms->prepareText('sms.order_canceled', [
                            'name' => $senderOrg->name,
                            'url'  => $order->getUrlForUser($recipient)
                        ]);
                        Yii::$app->sms->send($text, $recipient->profile->phone);
                    }
                }
            }
        }

        $systemMessage = $organization->name . \Yii::t('message', 'frontend.controllers.order.cancelled_order', ['ru' => ' отменил заказ!']);
        $this->sendSystemMessage($user, $order->id, $systemMessage, true);
    }

    /**
     * Заказ завершен
     *
     * @param Order $order
     * @param User  $user
     */
    public function doneOrder(Order $order, User $user)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $sender = $order->createdBy;
        $mailer = Yii::$app->mailer;
        $mailer->htmlLayout = '@api_web/views/mail/order';
        $senderOrg = $sender->organization;
        $subject = Yii::t('message', 'frontend.controllers.order.complete', ['ru' => "Заказ № {order_id} выполнен!", 'order_id' => $order->id]);

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification) {
                    if ($notification->order_done) {
                        $mailer->compose('@api_web/views/mail/orderDone', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }
                }

                $notification = $recipient->getSmsNotification($org);
                if ($notification) {
                    if (!empty($recipient->profile->phone) && $notification->order_done) {
                        $text = Yii::$app->sms->prepareText('sms.order_done', [
                            'name' => $order->vendor->name,
                            'url'  => $order->getUrlForUser($recipient)
                        ]);
                        Yii::$app->sms->send($text, $recipient->profile->phone);
                    }
                }
            }
        }

        $systemMessage = $order->client->name . \Yii::t('message', 'frontend.controllers.order.receive_order_five', ['ru' => ' получил заказ!']);
        $this->sendSystemMessage($user, $order->id, $systemMessage, false);
    }

    /**
     * Системные сообщения, с сохранением в чат
     *
     * @param      $user
     * @param      $order_id
     * @param      $message
     * @param bool $danger
     * @return bool
     * @throws \Exception
     */
    private function sendSystemMessage($user, $order_id, $message, $danger = false)
    {
        try {
            $order = Order::findOne(['id' => $order_id]);
            $newMessage = new OrderChat();
            $newMessage->order_id = $order->id;
            $newMessage->message = $message;
            $newMessage->is_system = 1;
            $newMessage->sent_by_id = $user->id;
            $newMessage->danger = $danger;
            $recipient_id = $order->client_id;
            if ($order->client_id == $user->organization->id) {
                $recipient_id = $order->vendor_id;
            }
            $newMessage->setAttribute('recipient_id', $recipient_id);
            $newMessage->save();

            $body = Yii::$app->controller->renderPartial('@frontend/views/order/_chat-message', [
                'name'      => '',
                'message'   => $newMessage->message,
                'time'      => $newMessage->created_at,
                'isSystem'  => 1,
                'sender_id' => $user->id,
                'ajax'      => 1,
                'danger'    => $danger,
            ]);

            $clientUsers = $order->client->users;
            $vendorUsers = $order->vendor->users;

            foreach ($clientUsers as $clientUser) {
                $channel = 'user' . $clientUser->id;
                Yii::$app->redis->executeCommand('PUBLISH', [
                    'channel' => 'chat',
                    'message' => Json::encode([
                        'body'     => $body,
                        'channel'  => $channel,
                        'isSystem' => 1,
                        'order_id' => $order_id,
                    ])
                ]);

                FireBase::getInstance()->update([
                    'user'          => $clientUser->id,
                    'organization'  => $newMessage->recipient_id,
                    'notifications' => uniqid(),
                ], [
                    'body'     => $newMessage->message,
                    'date'     => \Yii::$app->formatter->asDatetime('now', 'php:' . \DateTime::ATOM),
                    'order_id' => $order_id
                ]);
            }
            foreach ($vendorUsers as $vendorUser) {
                $channel = 'user' . $vendorUser->id;
                Yii::$app->redis->executeCommand('PUBLISH', [
                    'channel' => 'chat',
                    'message' => Json::encode([
                        'body'     => $body,
                        'channel'  => $channel,
                        'isSystem' => 1,
                        'order_id' => $order_id,
                    ])
                ]);

                FireBase::getInstance()->update([
                    'user'          => $vendorUser->id,
                    'organization'  => $newMessage->recipient_id,
                    'notifications' => uniqid(),
                ], [
                    'body'     => $newMessage->message,
                    'date'     => \Yii::$app->formatter->asDatetime('now', 'php:' . \DateTime::ATOM),
                    'order_id' => $order_id
                ]);
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Organization $organizaion
     * @return int
     */
    private function getNotificationCount(Organization $organizaion)
    {
        return (int)OrderChat::find()->where(['viewed' => 0, 'is_system' => 1, 'recipient_id' => $organizaion->id])->count();
    }

    /**
     * Sends email informing both sides about order change details
     *
     * @param Organization   $senderOrg
     * @param Order          $order
     * @param OrderContent[] $changed
     * @param OrderContent[] $deleted
     */
    public function sendOrderChange($senderOrg, $order, $changed = [], $deleted = [])
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        $mailer->htmlLayout = '@api_web/views/mail/order';
        // send email
        $subject = Yii::t('message', 'frontend.controllers.order.change_in_order', ['ru' => "Измененения в заказе №"]) . $order->id;

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;
        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification)
                    if ($notification->order_changed) {
                        $mailer->compose('orderChange', compact("subject", "senderOrg", "order", "dataProvider", "recipient", "changed", "deleted"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }
                $notification = $recipient->getSmsNotification($org);
                if ($notification)
                    if ($recipient->profile->phone && $notification->order_changed) {
                        $text = Yii::$app->sms->prepareText('sms.order_changed', [
                            'name' => $senderOrg->name,
                            'url'  => $order->getUrlForUser($recipient)
                        ]);
                        Yii::$app->sms->send($text, $recipient->profile->phone);
                    }
            }
        }

        /**
         * Отправка сообщения в чат
         */
        if ($senderOrg->id == $order->client_id) {
            $senderUser = $order->createdBy;
        } else {
            $senderUser = $order->acceptedBy ?? User::findOne(1);
        }

        $systemMessage = [];
        if (!empty($changed)) {
            $getterAttribute = function (OrderContent $model, string $attr) {
                $result = $model->$attr;
                if ($model->isAttributeChanged($attr)) {
                    $result = $model->getOldAttribute($attr) . ' => ' . $result;
                }
                return $result;
            };

            $systemMessage[] = \Yii::t('api_web', 'order.change.content');
            $oc = new  OrderContent();
            $systemMessage[] = implode(' | ', [
                $oc->getAttributeLabel('product_name'),
                $oc->getAttributeLabel('quantity'),
                $oc->getAttributeLabel('price'),
            ]);

            foreach ($changed as $orderContent) {
                $row = [];
                $row[] = $orderContent->product_name;
                $row[] = $getterAttribute($orderContent, 'quantity');
                $row[] = $getterAttribute($orderContent, 'price');
                $systemMessage[] = implode(' | ', $row) . PHP_EOL;
            }
        }

        if (!empty($deleted)) {
            $systemMessage[] = \Yii::t('api_web', 'order.delete.content');
            foreach ($deleted as $name) {
                $systemMessage[] = $name;
            }
        }

        $systemMessage = implode(PHP_EOL, $systemMessage);
        if (!empty($systemMessage)) {
            $systemMessage .= PHP_EOL . str_pad('', 20, '-');
            $systemMessage .= PHP_EOL . \Yii::t('api_web', 'order.notice.total_price') . ' ' . $order->total_price;
            $systemMessage .= ' ' . $order->currency->symbol;
            $this->sendSystemMessage($senderUser, $order->id, $systemMessage, false);
        }
    }
}
