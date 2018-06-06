<?php

namespace api_web\components\notice_class;

use api_web\classes\ChatWebApi;
use api_web\components\FireBase;
use common\models\Order;

class ChatNotice
{
    /**
     * Прочесть все сообщения, обновляем уведомления в FireBase
     * @param $organization_id
     */
    public function readAllMessages($organization_id)
    {
        $chat_web_api = new ChatWebApi();

        FireBase::getInstance()->update([
            'chat',
            'organization' => $organization_id,
        ], [
            'unread_message_count' => $chat_web_api->getUnreadMessageCount($organization_id),
            'unread_dialog_count' => $chat_web_api->dialogUnreadCount($organization_id)['result']
        ]);
    }

    /**
     * Отправляем уведомления что количество сообщений в диалоге и общее изменилось
     * @param $recipient_id
     * @param Order $order
     */
    public function addMessage($recipient_id, Order $order)
    {
        $chat_web_api = new ChatWebApi();

        FireBase::getInstance()->update([
            'chat',
            'organization' => $recipient_id,
            'dialog' => $order->id
        ], [
            'unread_message_count' => (int)$order->getOrderChatUnreadCount($recipient_id)
        ]);

        FireBase::getInstance()->update([
            'chat',
            'organization' => $recipient_id
        ], [
            'unread_message_count' => $chat_web_api->getUnreadMessageCount($recipient_id),
            'unread_dialog_count' => $chat_web_api->dialogUnreadCount($recipient_id)['result']
        ]);
    }
}