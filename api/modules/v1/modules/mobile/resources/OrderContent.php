<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderContent extends \common\models\OrderContent
{
    public function fields()
    {
        return ['id', 'order_id', 'product_id', 'quantity', 'price', 'initial_quantity', 'product_name', 'units', 'article'];
    }
    
     /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'product_id'], 'integer'],
            [['price', 'quantity', 'initial_quantity', 'units'], 'number'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogBaseGoods::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

}
