<?php

use yii\db\Migration;

/**
 * Class m181024_110146_add_index_to_email_queue
 */
class m181024_110146_add_index_to_email_queue extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('email_queue_to_idx', '{{%email_queue}}', 'to');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('email_queue_to_idx', '{{%email_queue}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181024_110146_add_index_to_email_queue cannot be reverted.\n";

        return false;
    }
    */
}
