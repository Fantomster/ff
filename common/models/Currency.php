<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "currency".
 *
 * @property integer $id
 * @property string $text
 * @property string $symbol
 *
 * @property Catalog[] $catalogs
 * @property Order[] $orders
 */
class Currency extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'currency';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text', 'symbol'], 'required'],
            [['text', 'symbol'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'text' => Yii::t('app', 'Text'),
            'symbol' => Yii::t('app', 'Symbol'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogs()
    {
        return $this->hasMany(Catalog::className(), ['currency_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['currency_id' => 'id']);
    }
}
