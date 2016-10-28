<?php

use yii\db\Migration;

class m161028_115242_units_null_in_order_content extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%order_content}}', 'units', $this->float()->null());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%order_content}}', 'units', $this->integer()->notNull());
    }
}
