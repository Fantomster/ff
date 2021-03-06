<?php

use yii\db\Migration;

/**
 * Class m181205_135405_alter_sms_error_table
 */
class m181205_135405_alter_sms_error_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable("{{%sms_error}}");
        $this->dropColumn("{{%sms_error}}", "message");
        $this->dropColumn("{{%sms_error}}", "target");
        $this->addColumn("{{%sms_error}}", "error_code", $this->integer()->notNull());
        $this->addColumn("{{%sms_error}}", "sms_id", $this->string()->notNull());
        $this->createIndex("idx_sms_error_sms_id", "{{%sms_error}}", "sms_id");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex("idx_sms_error_sms_id", "{{%sms_error}}");
        $this->dropColumn("{{%sms_error}}", "error_code");
        $this->dropColumn("{{%sms_error}}", "sms_send_id");
        $this->addColumn("{{%sms_error}}", "message", $this->string()->null());
        $this->addColumn("{{%sms_error}}", "target", $this->string()->null());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181205_135405_alter_sms_error_table cannot be reverted.\n";

        return false;
    }
    */
}
