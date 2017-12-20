<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "payment_type".
 *
 * @property integer $type_id
 * @property string $title
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Payment $payment
 */
class PaymentType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_type';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return ArrayHelper::merge(parent::behaviors(), [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function () {
                    return date("Y-m-d H:i:s");
                },
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $title = \Yii::t('app', 'app.models.PaymentType.type_payment');

        if($title == 'app.models.PaymentType.type_payment'){
            $title = Yii::t('app', 'Тип платежа');
        }

        return [
            'type_id' => 'Type ID',
            'title' => $title,
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
