<?php

use yii\db\Migration;

/**
 * Handles adding order_code to table `order`.
 */
class m180404_112149_add_order_code_column_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'order_code', $this->string(30));
        $orders = \common\models\Order::find()->all();

        foreach ($orders as &$order){
            $order->order_code = $order->id;
            $this->update('order', ['order_code'=>$order->id], ['id'=>$order->id]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'order_code');
    }
}
