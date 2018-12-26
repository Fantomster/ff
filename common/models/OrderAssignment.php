<?php

namespace common\models;

/**
 * This is the model class for table "order_assignment".
 *
 * @property int    $id           Идентификатор записи в таблице
 * @property int    $order_id     Идентификатор заказа
 * @property int    $assigned_to  Идентификатор пользователя, кому назначен заказ
 * @property int    $assigned_by  Идентификатор пользователя, кем назначен заказ
 * @property int    $is_processed Показатель состояния обработки заказа (0 - не обработан, 1 - обработан)
 * @property string $created_at   Дата и время создания записи в таблице
 * @property string $processed_at Дата и время обработки заказа
 *
 * @property User   $assignedBy
 * @property User   $assignedTo
 * @property Order  $order
 */
class OrderAssignment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_assignment}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'              => 'yii\behaviors\TimestampBehavior',
                'value'              => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
                'updatedAtAttribute' => false,
            ],
        ];
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
            'id'           => 'ID',
            'order_id'     => 'Order ID',
            'assigned_to'  => 'Назначен (кому)',
            'assigned_by'  => 'Назначен (кем)',
            'is_processed' => 'Обработан',
            'created_at'   => 'Created At',
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
