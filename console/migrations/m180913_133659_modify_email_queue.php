<?php

use yii\db\Migration;

/**
 * Class m180913_133659_modify_email_queue
 */
class m180913_133659_modify_email_queue extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%email_queue}}', 'created_at', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%email_queue}}', 'created_at', $this->timestamp()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180913_133659_modify_email_queue cannot be reverted.\n";

        return false;
    }
    */
}
