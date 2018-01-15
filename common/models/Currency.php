<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

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
class Currency extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'currency';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['text', 'symbol'], 'required'],
            [['text', 'symbol', 'num_code', 'iso_code'], 'string', 'max' => 255],
            [['is_active'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'text' => Yii::t('app', 'Text'),
            'symbol' => Yii::t('app', 'Symbol'),
            'is_active' => Yii::t('app', 'Активна?'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogs() {
        return $this->hasMany(Catalog::className(), ['currency_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders() {
        return $this->hasMany(Order::className(), ['currency_id' => 'id']);
    }

    /**
     * array of all currency types
     * 
     * @return array
     */
    public static function getList() {
        $models = Currency::find()
                ->select(['id', 'text'])
                ->where(['is_active'=>true])
                ->asArray()
                ->all();
        foreach ($models as &$model){
            $model['text'] = Yii::t('app', $model['text']);
        }
        return ArrayHelper::map($models, 'id', 'text');
    }
    
    /**
     * array of all currency symbols
     * 
     * @return array
     */
    public static function getSymbolList() {
        $models = Currency::find()
                ->select(['id', 'symbol'])
                ->where(['is_active'=>true])
                ->asArray()
                ->all();

        return ArrayHelper::map($models, 'id', 'symbol');
    }
}
