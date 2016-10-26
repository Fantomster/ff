<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_content".
 *
 * @property integer $order_id
 * @property integer $product_id
 * @property integer $quantity
 * @property integer $initial_quantity
 * @property string $price
 * @property string $product_name
 * @property integer $units
 *
 * @property Order $order
 * @property CatalogBaseGoods $product
 * @property string $total
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
            [['order_id', 'product_id', 'quantity', 'price', 'units', 'product_name'], 'required'],
            [['order_id', 'product_id', 'quantity', 'initial_quantity', 'units'], 'integer'],
            [['price'], 'number'],
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
}
