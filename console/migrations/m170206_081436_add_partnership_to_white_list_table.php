<?php

use yii\db\Migration;

class m170206_081436_add_partnership_to_white_list_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%white_list}}', 'partnership', $this->boolean()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%white_list}}', 'partnership');
    }
}
