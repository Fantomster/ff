<?php

use yii\db\Migration;

class m180702_143032_delete_columns_email_table_user extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%profile}}', 'email');
        $this->dropColumn('{{%profile}}', 'email_allow');
    }

    public function safeDown()
    {
        echo "m180702_143032_delete_columns_email_table_user cannot be reverted.\n";

        return false;
    }
}
