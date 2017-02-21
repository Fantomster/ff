<?php

use yii\db\Migration;

class m170213_150634_add_col_profile_sms_allow extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%profile}}', 'sms_allow', $this->boolean()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%profile}}', 'sms_allow');
    }
}
