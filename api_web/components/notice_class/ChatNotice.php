<?php

namespace api_web\components\notice_class;

use api_web\classes\ChatWebApi;
use api_web\components\FireBase;
use api_web\helpers\WebApiHelper;
use common\models\Order;

/**
 * Class ChatNotice
 *
 * @package api_web\components\notice_class
 */
class ChatNotice
{
    /**
     * Прочесть все сообщения, обновляем уведомления в FireBase
     *
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
            'unread_dialog_count'  => $chat_web_api->dialogUnreadCount($organization_id)['result']
        ]);
    }

    /**
     * Отправляем уведомления что количество сообщений в диалоге и общее изменилось
     *
     * @param       $recipient_id
     * @param Order $order
     * @param null  $messageFcm
     */
    public function updateCountMessageAndDialog($recipient_id, Order $order, $messageFcm = null)
    {
        $chat_web_api = new ChatWebApi();

        $last_message = $messageFcm ?? $order->orderChatLastMessage->message ?? 'Нет сообщений';
        if (!empty($last_message)) {
            $last_message = stripcslashes(trim($last_message, "'"));
        }
        $created_at = $order->orderChatLastMessage->created_at ?? null;

        FireBase::getInstance()->update([
            'chat',
            'organization' => $recipient_id,
            'dialog'       => $order->id
        ], [
            'unread_message_count' => (int)$order->getOrderChatUnreadCount($recipient_id),
            'last_message'         => $last_message,
            'last_message_date'    => WebApiHelper::asDatetime($created_at),
        ]);

        FireBase::unsetInstance();

        FireBase::getInstance()->update([
            'chat',
            'organization' => $recipient_id
        ], [
            'unread_message_count' => $chat_web_api->getUnreadMessageCount($recipient_id),
            'unread_dialog_count'  => $chat_web_api->dialogUnreadCount($recipient_id)['result']
        ]);
    }
}