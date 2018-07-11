<?php

use yii\db\Migration;

class m180629_114720_alter_table_profile extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%profile}}', 'gender', $this->tinyInteger(1)->null()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->alterColumn('{{%profile}}', 'gender', $this->tinyInteger(1)->null()->defaultValue(null));
    }

}
