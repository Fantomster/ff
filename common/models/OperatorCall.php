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
    const STATUS_OPEN = 1;//Открыто
    const STATUS_RECALL = 2; //Перезвонить
    const STATUS_COMPLETE = 3; //Завершено
    const STATUS_CONTROLL = 4; //Контроль

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
        if ($this->status_call_id == self::STATUS_COMPLETE) {
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
            [['status_call_id'], 'checkControlCount'],
            [['created_at', 'updated_at', 'closed_at'], 'safe'],
            [['comment'], 'string', 'max' => 255],
        ];
    }

    public function checkControlCount($attribute, $params)
    {
        if($attribute == 'status_call_id') {
            if ($this->$attribute == 4) {
                if (self::find()->where(['operator_id' => $this->operator_id, $attribute => self::STATUS_CONTROLL])->count() == Yii::$app->params['countControlsOperator']) {
                    $this->addError($attribute, "Одновременно на контроле может быть не более ".Yii::$app->params['countControlsOperator']." заказаов!");
                }
            }
        }
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
            self::STATUS_OPEN => 'Открыто',
            self::STATUS_RECALL => 'Перезвонить',
            self::STATUS_COMPLETE => 'Завершено',
            self::STATUS_CONTROLL => 'Контроль'
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
