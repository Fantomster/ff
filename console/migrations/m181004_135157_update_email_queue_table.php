<?php

use yii\db\Migration;

/**
 * Class m181004_135157_update_email_queue_table
 */
class m181004_135157_update_email_queue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn("{{%email_queue}}", 'body', 'LONGTEXT NULL DEFAULT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn("{{%email_queue}}", 'body', $this->text()->null());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181004_135157_update_email_queue_table cannot be reverted.\n";

        return false;
    }
    */
}
