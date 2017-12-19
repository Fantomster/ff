<?php

use yii\db\Migration;

class m171107_095100_fk_cg_cat extends Migration
{
    public function safeUp()
    {
        $this->addForeignKey('{{%fk_cg_cat}}', '{{%catalog_goods}}', 'cat_id', '{{%catalog}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_cg_cat}}', '{{%catalog_goods}}');
    }
}
