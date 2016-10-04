<?php

use yii\db\Migration;

class m161003_080533_back_to_decimal extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%order}}', 'total_price', $this->decimal(10,2)->defaultValue(0));
        $this->alterColumn('{{%order_content}}', 'price', $this->decimal(10,2)->defaultValue(0));
    }

    public function safeDown()
    {
        $this->alterColumn('{{%order}}', 'total_price', $this->bigInteger());
        $this->alterColumn('{{%order_content}}', 'price', $this->bigInteger());
    }
}
