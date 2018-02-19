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


    public function getCurrencyData($filter_currency, $franchId, $orgField = 'client_id'):array
    {
        $array = [];
        $iso_code = "RUB";
        $array['currency_id'] = 1;

        //Список валют из заказов
        $currency_list = Order::find()->distinct()->select([
            'order.currency_id',
            'order.client_id',
            'order.vendor_id',
            'c.id',
            'c.iso_code',
            'COUNT(order.id) as count'
        ])->joinWith('currency as c')
            ->join('LEFT JOIN', 'franchisee_associate as fa', 'fa.organization_id = order.'.$orgField)
            ->where('status <> :status',[':status' => Order::STATUS_FORMING])
            ->andWhere('fa.franchisee_id = :fid', [':fid' => $franchId])
            ->groupBy('iso_code')
            ->asArray()->all();

        $currencyList = ['1' => 'RUB'];

        foreach($currency_list as $c) {
            $currencyList[$c['id']] = $c['iso_code'] . ' (заказов ' . $c['count'] . ')';
        }
        $array['currency_list'] = $currencyList;

        if($filter_currency) {
            $currency = Currency::findOne($filter_currency);
            $iso_code = $currency->iso_code;
            $array['currency_id'] = $filter_currency;
        }
        $array['iso_code'] = $iso_code;
        return $array;
    }
}
