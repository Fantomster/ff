<?php

use yii\db\Migration;

class m180625_084356_add_columns_table_profile extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%profile}}', 'job_id', $this->tinyInteger(2)->null()->defaultValue(null));
        $this->addColumn('{{%profile}}', 'gender', $this->tinyInteger(1)->null()->defaultValue(null));
        $this->addColumn('{{%profile}}', 'email', $this->string(50)->null()->defaultValue(null));
        $this->addColumn('{{%profile}}', 'email_allow', $this->tinyInteger(1)->null()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%profile}}', 'job_id');
        $this->dropColumn('{{%profile}}', 'gender');
        $this->dropColumn('{{%profile}}', 'email');
        $this->dropColumn('{{%profile}}', 'email_allow');
    }

}
