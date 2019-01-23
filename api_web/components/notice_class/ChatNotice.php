<?php

namespace api_web\components\notice_class;

use api_web\components\FireBase;
use api_web\helpers\WebApiHelper;
use common\models\Order;
use common\models\OrderChat;

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
        FireBase::getInstance()->update([
            'chat',
            'organization' => $organization_id,
        ], [
            'unread_message_count' => self::getUnreadMessageCount($organization_id),
            'unread_dialog_count'  => self::dialogUnreadCount($organization_id)['result']
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

        FireBase::getInstance()->update([
            'chat',
            'organization' => $recipient_id
        ], [
            'unread_message_count' => self::getUnreadMessageCount($recipient_id),
            'unread_dialog_count'  => self::dialogUnreadCount($recipient_id)['result']
        ]);
    }

    /**
     * Количество не прочитанных сообщений
     *
     * @param null $r_id
     * @return int
     */
    public static function getUnreadMessageCount($r_id = null)
    {
        return (int)OrderChat::find()->where(['viewed' => 0, 'recipient_id' => $r_id])->count();
    }

    /**
     * Число диалогов с новыми сообщениями
     *
     * @param null $r_id
     * @return int|string
     */
    public static function dialogUnreadCount($r_id = null)
    {
        return OrderChat::find()
            ->select('order_id')
            ->where(['viewed' => 0, 'recipient_id' => $r_id])
            ->groupBy('order_id')
            ->count();
    }
}
