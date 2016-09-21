<?php

use yii\db\Migration;

class m160912_093020_alter_order_related_tables extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_content}}', 'id', $this->primaryKey()->first());
        $this->alterColumn('{{%order}}', 'total_price', $this->bigInteger());
        $this->alterColumn('{{%order_content}}', 'price', $this->bigInteger());
        $this->addColumn('{{%order_content}}', 'accepted_quantity', $this->integer());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%order}}', 'total_price', $this->decimal());
        $this->alterColumn('{{%order_content}}', 'price', $this->decimal());
        $this->dropColumn('{{%order_content}}', 'accepted_quantity');
        $this->dropColumn('{{%order_content}}', 'id');
    }
}
