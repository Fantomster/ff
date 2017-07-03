<?php

use yii\db\Migration;

class m170703_130846_alter_order_total_price extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%order}}', 'total_price', $this->decimal(20,2)->defaultValue(0));
    }

    public function safeDown()
    {
        $this->alterColumn('{{%order}}', 'total_price', $this->decimal(10,2)->defaultValue(0));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170703_130846_alter_order_total_price cannot be reverted.\n";

        return false;
    }
    */
}
