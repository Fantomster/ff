<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order".
 *
 * @property integer $id
 * @property integer $order_id
 */
class OrderSequence extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */

    public static function tableName(): string
    {
        return 'order_sequence';
    }


    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['order_id'], 'required'],
            [['order_id'], 'integer'],
            [['order_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'order_id' => Yii::t('app', 'Номер заказа'),
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder(): object
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

}
