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


    public function getCurrencyData($filter_currency, $franchId, $orgField = 'client_id', $date_from, $date_to):array
    {
        $array = [];
        $iso_code = "RUB";
        $array['currency_id'] = 1;
        $currencyList = [];

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
            ->where('status <> :status',[':status' => OrderStatus::STATUS_FORMING])
            ->andWhere('fa.franchisee_id = :fid', [':fid' => $franchId])
            ->andWhere(['between', 'DATE(order.created_at)', date('Y-m-d', strtotime($date_from)), date('Y-m-d', strtotime($date_to))])
            ->orderBy('count DESC')
            ->groupBy('iso_code')
            ->asArray()->all();

        $i=0;
        foreach($currency_list as $c) {
            if($i==0){
                $iso_code = $c['iso_code'];
            }
            $currencyList[$c['id']] = $c['iso_code'] . ' (' . Yii::t('app', 'frontend.views.client.index.orders_new') . " " . $c['count'] . ')';
            $i++;
        }
        if(count($currencyList)){
            $array['currency_id'] = key($currencyList);
        }
        $array['currency_list'] = $currencyList;
        if($filter_currency) {
            $currency = Currency::findOne($filter_currency);
            $iso_code = $currency->iso_code ?? 'RUB';
            $array['currency_id'] = $filter_currency;
        }
        $array['iso_code'] = $iso_code;
        return $array;
    }


    public static function getFullCurrencyList($franchId):array
    {
        $array = [];
        $filter_from_date = \Yii::$app->request->get('filter_from_date') ? trim(\Yii::$app->request->get('filter_from_date')) : date("d-m-Y", strtotime(" -1 months"));
        $filter_to_date = \Yii::$app->request->get('filter_to_date') ? trim(\Yii::$app->request->get('filter_to_date')) : date("d-m-Y");
        //Список валют из заказов
        $currency_list = Order::find()->distinct()->select([
            'order.currency_id',
            'order.client_id',
            'order.vendor_id',
            'c.id',
            'c.iso_code',
            'COUNT(order.id) as count'
        ])->joinWith('currency as c')
            ->join('LEFT JOIN', 'franchisee_associate as fa1', 'fa1.organization_id = order.client_id')
            ->join('LEFT JOIN', 'franchisee_associate as fa2', 'fa2.organization_id = order.vendor_id')
            ->where('status <> :status',[':status' => OrderStatus::STATUS_FORMING])
            ->andWhere('fa1.franchisee_id = :fid1', [':fid1' => $franchId])
            ->andWhere('fa2.franchisee_id = :fid2', [':fid2' => $franchId])
            ->andWhere(['between', 'DATE(order.created_at)', date('Y-m-d', strtotime($filter_from_date)), date('Y-m-d', strtotime($filter_to_date))])
            ->orderBy('count DESC')
            ->groupBy('iso_code')
            ->asArray()->all();

        foreach($currency_list as $c) {
            $array[$c['id']] = $c['iso_code'] . ' (' . Yii::t('app', 'frontend.views.client.index.orders') . " " . $c['count'] . ')';
        }

        return $array;
    }


    public function getAnalCurrencyList($organizationId, $filter_from_date, $filter_to_date, $field = 'client_id'):array
    {
        //Список валют из заказов
        $currency_list = Order::find()->distinct()->select([
            'order.currency_id',
            'c.id',
            'c.iso_code',
            'COUNT(order.id) as count'
        ])->joinWith('currency as c')
            ->where('status <> :status',[':status' => OrderStatus::STATUS_FORMING])
            ->andWhere("$field = :cid", [':cid' => $organizationId])
            ->andWhere(['between', 'DATE(created_at)', date('Y-m-d', strtotime($filter_from_date)), date('Y-m-d', strtotime($filter_to_date))])
            ->orderBy('count DESC')
            ->groupBy('iso_code')
            ->asArray()->all();

        $currencyList = [];

        foreach($currency_list as $c) {
            $currencyList[$c['id']] = $c['iso_code'] . ' (' . Yii::t('app', 'frontend.views.client.index.orders') . " " . $c['count'] . ')';
        }

        return $currencyList;
    }


    public static function getMostPopularIsoCode($franchId):string
    {
        $filter_from_date = \Yii::$app->request->get('filter_from_date') ? trim(\Yii::$app->request->get('filter_from_date')) : date("d-m-Y", strtotime(" -1 months"));
        $filter_to_date = \Yii::$app->request->get('filter_to_date') ? trim(\Yii::$app->request->get('filter_to_date')) : date("d-m-Y");
        //Список валют из заказов
        $currency_one = Order::find()->distinct()->select([
            'order.currency_id',
            'order.client_id',
            'order.vendor_id',
            'c.id',
            'c.iso_code',
            'COUNT(order.id) as count'
        ])->joinWith('currency as c')
            ->join('LEFT JOIN', 'franchisee_associate as fa1', 'fa1.organization_id = order.client_id')
            ->join('LEFT JOIN', 'franchisee_associate as fa2', 'fa2.organization_id = order.vendor_id')
            ->where('status <> :status',[':status' => OrderStatus::STATUS_FORMING])
            ->andWhere('fa1.franchisee_id = :fid1', [':fid1' => $franchId])
            ->andWhere('fa2.franchisee_id = :fid2', [':fid2' => $franchId])
            ->andWhere(['between', 'DATE(order.created_at)', date('Y-m-d', strtotime($filter_from_date)), date('Y-m-d', strtotime($filter_to_date))])
            ->orderBy('count DESC')
            ->groupBy('iso_code')
            ->asArray()->one();
        return $currency_one['iso_code'] ?? "RUB";
    }
}
