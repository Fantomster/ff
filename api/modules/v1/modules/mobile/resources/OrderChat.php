<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderChat extends \common\models\OrderChat
{
    public function fields()
    {
        return ['id', 'order_id', 'sent_by_id', 'is_system', 'message', 'created_at', 'viewed', 'recipient_id', 'danger'];
    }
    
     /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'sent_by_id', 'viewed', 'recipient_id'], 'integer'],
            [['message', 'created_at', 'is_system', 'danger'], 'safe'],
            [['message'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process', 'on' => 'userSent'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['sent_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['sent_by_id' => 'id']],
        ];
    }

}
