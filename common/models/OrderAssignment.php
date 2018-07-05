<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_assignment".
 *
 * @property int $id
 * @property int $order_id
 * @property int $assigned_to
 * @property int $assigned_by
 * @property int $is_processed
 * @property string $created_at
 * @property string $processed_at
 *
 * @property User $assignedBy
 * @property User $assignedTo
 * @property Order $order
 */
class OrderAssignment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_assignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'assigned_to', 'assigned_by'], 'required'],
            [['order_id', 'assigned_to', 'assigned_by', 'is_processed'], 'integer'],
            [['created_at', 'processed_at'], 'safe'],
            [['assigned_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['assigned_by' => 'id']],
            [['assigned_to'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['assigned_to' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'assigned_to' => 'Assigned To',
            'assigned_by' => 'Assigned By',
            'is_processed' => 'Is Processed',
            'created_at' => 'Created At',
            'processed_at' => 'Processed At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssignedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'assigned_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssignedTo()
    {
        return $this->hasOne(User::className(), ['id' => 'assigned_to']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
}
