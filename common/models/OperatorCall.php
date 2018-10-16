<?php

namespace common\models;

use Yii;
use yii\behaviors\AttributesBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%operator_call}}".
 *
 * @property int $order_id
 * @property int $operator_id
 * @property int $status_call_id
 * @property string $comment
 * @property string $created_at
 * @property string $updated_at
 * @property string $closed_at
 */
class OperatorCall extends ActiveRecord
{
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ]
        ];
    }

    public static function primaryKey()
    {
        return ['order_id'];
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->status_call_id == 3) {
            $this->setAttribute('closed_at', \gmdate("Y-m-d H:i:s"));
        }

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%operator_call}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['operator_id'], 'required'],
            [['operator_id', 'status_call_id'], 'integer'],
            [['created_at', 'updated_at', 'closed_at'], 'safe'],
            [['comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id'       => Yii::t('app', 'Order ID'),
            'operator_id'    => Yii::t('app', 'Operator ID'),
            'status_call_id' => Yii::t('app', 'Status Call ID'),
            'comment'        => Yii::t('app', 'Comment'),
            'created_at'     => Yii::t('app', 'Created At'),
            'updated_at'     => Yii::t('app', 'Updated At'),
            'closed_at'      => Yii::t('app', 'Closed At'),
        ];
    }

    /**
     * @return array
     */
    public static function getStatus()
    {
        return [
            '1' => 'Открыто',
            '2' => 'Перезвонить',
            '3' => 'Завершено',
        ];
    }

    /**
     * @param $id
     * @return string
     */
    public static function getStatusText($id)
    {
        return self::getStatus()[$id] ?? '';
    }
}
