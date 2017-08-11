<?php

use yii\db\Migration;

class m170801_102738_remove_mail_queue extends Migration
{
    public function safeUp()
    {
        $this->dropTable('{{%mail_queue}}');
    }

    public function safeDown()
    {
        echo "m170801_102738_remove_mail_queue cannot be reverted.\n";

        return false;
    }
}
