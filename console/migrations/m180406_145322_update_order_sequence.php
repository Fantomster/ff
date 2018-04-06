<?php

use yii\db\Migration;

/**
 * Class m180406_145322_update_order_sequence
 */
class m180406_145322_update_order_sequence extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $orders = \common\models\Order::find()->all();

        foreach ($orders as &$order){
            $order->order_code = $order->id;
            $this->update('order', ['order_code'=>$order->id], ['id'=>$order->id]);
        }
        $this->alterColumn('order', 'order_code', 'integer');
        $max = \common\models\Order::find()->select('id')->max('id');
        $min = \common\models\Order::find()->select('id')->min('id');
        $max++;
        $this->insert('order_sequence', ['id'=>$max, 'order_id'=>$min]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180406_145322_update_order_sequence cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180406_145322_update_order_sequence cannot be reverted.\n";

        return false;
    }
    */
}
