<?php

use yii\db\Migration;

/**
 * Class m180703_133512_add_fields_to_additional_email_table
 */
class m180703_133512_add_fields_to_additional_email_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%additional_email}}', 'confirmed', $this->tinyInteger(1)->defaultValue(0));
        $this->addColumn('{{%additional_email}}', 'token', $this->string(255)->null());
        $this->alterColumn('{{%additional_email}}', 'order_created', $this->integer()->defaultValue(0));
        $this->alterColumn('{{%additional_email}}', 'order_canceled', $this->integer()->defaultValue(0));
        $this->alterColumn('{{%additional_email}}', 'order_changed', $this->integer()->defaultValue(0));
        $this->alterColumn('{{%additional_email}}', 'order_processing', $this->integer()->defaultValue(0));
        $this->alterColumn('{{%additional_email}}', 'order_done', $this->integer()->defaultValue(0));
        $this->update('{{%additional_email}}', ['confirmed' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%additional_email}}', 'confirmed');
        $this->dropColumn('{{%additional_email}}', 'token');
        $this->alterColumn('{{%additional_email}}', 'order_created', $this->integer()->defaultValue(1));
        $this->alterColumn('{{%additional_email}}', 'order_canceled', $this->integer()->defaultValue(1));
        $this->alterColumn('{{%additional_email}}', 'order_changed', $this->integer()->defaultValue(1));
        $this->alterColumn('{{%additional_email}}', 'order_processing', $this->integer()->defaultValue(1));
        $this->alterColumn('{{%additional_email}}', 'order_done', $this->integer()->defaultValue(1));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180703_133512_add_fields_to_additional_email_table cannot be reverted.\n";

        return false;
    }
    */
}
