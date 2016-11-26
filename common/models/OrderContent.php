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
 *
 * @property Order $order
 * @property CatalogBaseGoods $product
 * @property string $total
 * @property string $note
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
            'quantity' => 'Количество',
            'initial_quantity' => 'Запрошенное количество',
            'price' => 'Цена',
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
    
    public function getNote() {
        return $this->hasOne(GoodsNotes::className(), ['id' => 'catalog_base_goods_id', 'rest_org_id' => $this->order->client_id]);
    }
}
