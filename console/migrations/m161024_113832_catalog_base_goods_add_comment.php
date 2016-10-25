<?php

use yii\db\Migration;

class m161024_113832_catalog_base_goods_add_comment extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%catalog_base_goods}}', 'note', $this->string()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%catalog_base_goods}}', 'note');
    }
}
