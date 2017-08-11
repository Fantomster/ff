<?php

use yii\db\Migration;

class m170802_130923_create_email_blacklist extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%email_blacklist}}', [
            'id' => $this->primaryKey(),
            'email' => $this->string()->notNull()->unique(),
        ], $tableOptions);
        $this->dropColumn('{{%email_notification}}', 'active');
        $this->dropColumn('{{%email_notification}}', 'last_fail');
        $this->dropColumn('{{%sms_notification}}', 'active');
    }

    public function safeDown()
    {
        $this->addColumn('{{%email_notification}}', 'active', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%email_notification}}', 'last_fail', $this->integer()->null());
        $this->addColumn('{{%sms_notification}}', 'active', $this->boolean()->notNull()->defaultValue(false));
        $this->dropTable('{{%email_blacklist}}');
    }
}
