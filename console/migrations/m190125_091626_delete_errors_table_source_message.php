<?php

use yii\db\Migration;

class m190125_091626_delete_errors_table_source_message extends Migration
{
    public function safeUp()
    {
        $this->delete('{{%source_message}}', ['like', 'message', 'SQLSTATE%']);
        $this->delete('{{%source_message}}', ['like', 'message', 'fopen(%']);
    }

    public function safeDown()
    {
        echo "m190125_091626_delete_errors_table_source_message cannot be reverted.\n";

        return false;
    }
}
