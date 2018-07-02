<?php

use yii\db\Migration;

class m180702_113452_add_column_sms_subscribe_table_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'sms_subscribe', $this->integer()->notNull()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'blacklisted');
    }

}
