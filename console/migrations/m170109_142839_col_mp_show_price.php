<?php

use yii\db\Migration;

class m170109_142839_col_mp_show_price extends Migration
{
     // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%catalog_base_goods}}', 'mp_show_price', $this->integer()->notNull()->defaultValue(0));
    }
    public function safeDown()
    {
        $this->dropColumn('{{%catalog_base_goods}}', 'mp_show_price');
    }
}
