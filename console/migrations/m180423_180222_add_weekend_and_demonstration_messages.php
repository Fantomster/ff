<?php

use yii\db\Migration;

/**
 * Class m180423_180222_add_weekend_and_demonstration_messages
 */
class m180423_180222_add_weekend_and_demonstration_messages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'send_week_message', $this->integer()->defaultValue(0));
        $this->addColumn('{{%user}}', 'send_demo_message', $this->integer()->defaultValue(0));
        $this->update('{{%user}}', ['send_week_message' => 1]);
        $this->update('{{%user}}', ['send_demo_message' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'send_week_message');
        $this->dropColumn('{{%user}}', 'send_demo_message');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180423_180222_add_weekend_and_demonstration_messages cannot be reverted.\n";

        return false;
    }
    */
}
