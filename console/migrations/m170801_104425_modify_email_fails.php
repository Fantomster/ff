<?php

use yii\db\Migration;

class m170801_104425_modify_email_fails extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('{{%fk_email_fails}}', '{{%email_fails}}');
        $this->dropColumn('{{%email_fails}}', 'email_notification_id');
    }

    public function safeDown()
    {
        $this->addColumn('{{%email_fails}}', 'email_notification_id', $this->integer()->null());
        $this->addForeignKey('{{%fk_email_fails}}', '{{%email_fails}}', 'email_notification_id', '{{%email_notification}}', 'id');
    }
}
