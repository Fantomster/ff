<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderChat extends \common\models\OrderChat
{
    public $type; //dialogs or messages
    public $count;
    public $page;
    public $organization_picture;
    public $organization_name;

    const TYPE_DIALOGS = 1;
    const TYPE_MESSAGES = 2;

    public function fields()
    {
        return [
            'id',
            'order_id',
            'sent_by_id',
            'is_system',
            'message',
            'created_at',
            'viewed',
            'recipient_id',
            'danger',
            'type',
            'count',
            'page',
            'organization_picture',
            'organization_name'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'sent_by_id', 'viewed', 'recipient_id', 'count', 'type', 'page'], 'integer'],
            [['message', 'created_at', 'is_system', 'danger', 'organization_name', 'organization_picture'], 'safe'],
            [['message'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process', 'on' => 'userSent'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['sent_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['sent_by_id' => 'id']],
        ];
    }

    public static function sendChatMessage($user, $order_id, $message)
    {
        $order = \common\models\Order::findOne(['id' => $order_id]);

        $newMessage = new \common\models\OrderChat(['scenario' => 'userSent']);
        $newMessage->order_id = $order_id;
        $newMessage->sent_by_id = $user->id;
        $newMessage->message = $message;
        if ($order->client_id == $user->organization_id) {
            $newMessage->recipient_id = $order->vendor_id;
        } else {
            $newMessage->recipient_id = $order->client_id;
        }
        if (!$newMessage->save())

            return false;

        return true;
    }

}
