<?php

use yii\db\Migration;

class m161028_113719_CBG_add_ed extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%catalog_base_goods}}', 'ed', $this->string()->notNull());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%catalog_base_goods}}', 'ed');
    }
}
