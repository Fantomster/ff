<?php

use yii\db\Migration;

/**
 * Class m181206_160505_change_sms_error
 */
class m181206_160505_change_sms_error extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex("idx_sms_error_sms_id", "{{%sms_error}}");
        $this->dropColumn("{{%sms_error}}", "sms_id");
        $this->addColumn("{{%sms_error}}", "sms_send_id", $this->integer()->null());
        $this->createIndex("idx_sms_error_sms_send_id", "{{%sms_error}}", "sms_send_id");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex("idx_sms_error_sms_send_id", "{{%sms_error}}");
        $this->dropColumn("{{%sms_error}}", "sms_send_id");
        $this->addColumn("{{%sms_error}}", "sms_id", $this->string()->null());
        $this->createIndex("idx_sms_error_sms_id", "{{%sms_error}}", "sms_id");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181206_160505_change_sms_error cannot be reverted.\n";

        return false;
    }
    */
}
