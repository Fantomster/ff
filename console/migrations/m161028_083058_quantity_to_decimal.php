<?php

use yii\db\Migration;

class m161028_083058_quantity_to_decimal extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%order_content}}', 'quantity', $this->decimal(10,1)->notNull());
        $this->alterColumn('{{%order_content}}', 'initial_quantity', $this->decimal(10,1)->notNull());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%order_content}}', 'quantity', $this->integer()->notNull());
        $this->alterColumn('{{%order_content}}', 'initial_quantity', $this->integer()->notNull());
    }
}
