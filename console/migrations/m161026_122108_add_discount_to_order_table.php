<?php

use yii\db\Migration;

class m161026_122108_add_discount_to_order_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'discount', $this->decimal(10,2)->null());
        $this->addColumn('{{%order}}', 'discount_type', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'discount');
        $this->dropColumn('{{%order}}', 'discount_type');
    }
}
