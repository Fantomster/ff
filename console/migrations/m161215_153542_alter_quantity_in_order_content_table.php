<?php

use yii\db\Migration;

class m161215_153542_alter_quantity_in_order_content_table extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%order_content}}', 'quantity', $this->decimal(10,3)->notNull());
        $this->alterColumn('{{%order_content}}', 'initial_quantity', $this->decimal(10,3)->null());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%order_content}}', 'quantity', $this->decimal(10,1)->notNull());
        $this->alterColumn('{{%order_content}}', 'initial_quantity', $this->decimal(10,1)->null());
    }
}
