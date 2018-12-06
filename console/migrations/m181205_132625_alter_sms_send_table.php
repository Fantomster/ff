<?php

use yii\db\Migration;

/**
 * Class m181205_132625_alter_sms_send_table
 */
class m181205_132625_alter_sms_send_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("{{%sms_send}}", "order_id", $this->integer()->null());
        $this->createIndex("idx_sms_send_sms_id", "{{%sms_send}}", "sms_id");
        $this->createIndex("idx_sms_send_order_id", "{{%sms_send}}", "order_id");
        $this->createIndex("idx_sms_send_target", "{{%sms_send}}", "target");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex("idx_sms_send_sms_id", "{{%sms_send}}");
        $this->dropIndex("idx_sms_send_order_id", "{{%sms_send}}");
        $this->dropIndex("idx_sms_send_target", "{{%sms_send}}");
        $this->dropColumn("{{%sms_send}}", "order_id");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181205_132625_alter_sms_send_table cannot be reverted.\n";

        return false;
    }
    */
}
