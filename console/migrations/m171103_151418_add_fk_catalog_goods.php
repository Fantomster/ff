<?php

use yii\db\Migration;

class m171103_151418_add_fk_catalog_goods extends Migration
{
    public function safeUp()
    {
        $this->addForeignKey('{{%fk_catalog_goods}}', '{{%catalog_goods}}', 'base_goods_id', '{{%catalog_base_goods}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_catalog_goods}}', '{{%catalog_goods}}');
    }
}
