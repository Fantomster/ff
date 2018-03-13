<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_content".
 *
 * @property integer $order_id
 * @property integer $product_id
 * @property string $quantity
 * @property string $initial_quantity
 * @property string $price
 * @property string $product_name
 * @property integer $units
 * @property string $comment
 *
 * @property Order $order
 * @property CatalogBaseGoods $product
 * @property string $total
 * @property string $note
 * @property CatalogGoods $productFromCatalog
 */
class OrderContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'product_id', 'quantity', 'price', 'product_name'], 'required'],
            [['order_id', 'product_id'], 'integer'],
            [['price', 'quantity', 'initial_quantity', 'units'], 'number'],
            [['comment'], 'safe'],
            [['comment'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogBaseGoods::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'product_id' => 'Product ID',
            'quantity' => Yii::t('app','common.models.amount', ['ru'=>'Количество']),
            'initial_quantity' => Yii::t('app','common.models.asked_amount', ['ru'=>'Запрошенное количество']),
            'price' => Yii::t('app','common.models.price_three', ['ru'=>'Цена']),
            'total' => Yii::t('app','common.models.sum', ['ru'=>'Сумма']),
            'comment' => 'Comment',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(CatalogBaseGoods::className(), ['id' => 'product_id']);
    }
    
    public function getTotal() {
        return $this->quantity * $this->price;
    }
    
    public function getProductFromCatalog() {
        $cgTable = CatalogGoods::tableName();
        $cbgTable = CatalogBaseGoods::tableName();
        $orgTable = Organization::tableName();
        $rsrTable = RelationSuppRest::tableName();
        $catTable = Catalog::tableName();
        
        $product = CatalogGoods::find()
                ->leftJoin($cbgTable, "$cbgTable.id = $cgTable.base_goods_id")
                ->leftJoin($orgTable, "$orgTable.id = $cbgTable.supp_org_id")
                ->leftJoin($rsrTable, "$rsrTable.cat_id = $cgTable.cat_id")
                ->leftJoin($catTable, "$catTable.id = $rsrTable.cat_id")
                ->where([
                    "$rsrTable.status" => RelationSuppRest::CATALOG_STATUS_ON,
                    "$rsrTable.deleted" => false,
                    "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF,
                    "$cbgTable.status" => CatalogBaseGoods::STATUS_ON,
                    "$rsrTable.supp_org_id" => $this->order->vendor_id,
                    "$rsrTable.rest_org_id" => $this->order->client_id,
                    "$catTable.status" => Catalog::STATUS_ON,
                    "$cbgTable.id" => $this->product_id,
                ])
                ->one();
        return $product;
    }
    
    public function copyIfPossible() {
        $cgTable = CatalogGoods::tableName();
        $cbgTable = CatalogBaseGoods::tableName();
        $orgTable = Organization::tableName();
        $rsrTable = RelationSuppRest::tableName();
        $catTable = Catalog::tableName();
        
        $product = CatalogGoods::find()
                ->leftJoin($cbgTable, "$cbgTable.id = $cgTable.base_goods_id")
                ->leftJoin($orgTable, "$orgTable.id = $cbgTable.supp_org_id")
                ->leftJoin($rsrTable, "$rsrTable.cat_id = $cgTable.cat_id")
                ->leftJoin($catTable, "$catTable.id = $rsrTable.cat_id")
                ->where([
                    "$rsrTable.deleted" => false,
                    "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF,
                    "$cbgTable.status" => CatalogBaseGoods::STATUS_ON,
                    "$rsrTable.supp_org_id" => $this->order->vendor_id,
                    "$rsrTable.rest_org_id" => $this->order->client_id,
                    "$catTable.status" => Catalog::STATUS_ON,
                    "$cbgTable.id" => $this->product_id,
                ])
                ->one();
        if ($product) {
            return [
                'product_id' => $product->baseProduct->id,
                'quantity' => $this->quantity,
                'price' => $product->price,
                'product_name' => $product->baseProduct->product,
                'units' => $product->baseProduct->units,
                'article' => $product->baseProduct->article,
            ];
        }
        $product = CatalogBaseGoods::find()
                ->leftJoin($orgTable, "$orgTable.id = $cbgTable.supp_org_id")
                ->leftJoin($rsrTable, "$rsrTable.cat_id = $cbgTable.cat_id")
                ->leftJoin($catTable, "$catTable.id = $rsrTable.cat_id")
                ->where([
                    "$rsrTable.deleted" => false,
                    "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF,
                    "$cbgTable.status" => CatalogBaseGoods::STATUS_ON,
                    "$rsrTable.supp_org_id" => $this->order->vendor_id,
                    "$rsrTable.rest_org_id" => $this->order->client_id,
                    "$catTable.status" => Catalog::STATUS_ON,
                    "$cbgTable.id" => $this->product_id,
                ])
                ->one();
        if ($product) {
            return [
                'product_id' => $product->id,
                'quantity' => $this->quantity,
                'price' => $product->price,
                'product_name' => $product->product,
                'units' => $product->units,
                'article' => $product->article,
            ];
        }
        return [];
    }
    
    public function  getNote() {
        return GoodsNotes::findOne(['catalog_base_goods_id' => $this->product_id, 'rest_org_id' => $this->order->client_id]);
    }
    
    public function formatPrice() {
        return $this->price . " " . $this->order->currency->symbol;
    }
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        
        $product = $this->productFromCatalog;
        if (!empty($product)) {
            $catalog = $product->catalog;
        } else {
            $catalog = $this->product->catalog;
        }
        
        if ($catalog->currency_id !== $this->order->currency_id) {
            $order = $this->order;
            $order->currency_id = $catalog->currency_id;
            $order->save();
        }
        
        if (!is_a(Yii::$app, 'yii\console\Application')) {
            if($this->order->status == Order::STATUS_FORMING)
                \api\modules\v1\modules\mobile\components\notifications\NotificationCart::actionCartContent($this->id);
        }
    }

    public function getCurrency() {
         return Currency::findOne($this->order->currency_id);
    }

    public function beforeDelete() {
        parent::beforeDelete();

        if (!is_a(Yii::$app, 'yii\console\Application')) {
            if($this->order->status == Order::STATUS_FORMING)
                \api\modules\v1\modules\mobile\components\notifications\NotificationCart::actionCartContentDelete($this->id);
        }
    }
}
