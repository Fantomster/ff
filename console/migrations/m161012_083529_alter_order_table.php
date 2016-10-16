<?php

use yii\db\Migration;

class m161012_083529_alter_order_table extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%order}}', 'created_by_id', $this->integer()->null());
        $this->addColumn('{{%order_content}}', 'product_name', $this->string()->notNull());
        $this->addColumn('{{%order_content}}', 'units', $this->integer()->notNull());
        $this->addColumn('{{%order_content}}', 'article', $this->string()->null());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%order}}', 'created_by_id', $this->integer()->notNull());
        $this->dropColumn('{{%order_content}}', 'product_name');
        $this->dropColumn('{{%order_content}}', 'units');
        $this->dropColumn('{{%order_content}}', 'article');
    }
}
