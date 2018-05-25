<?php

use yii\db\Migration;

/**
 * Class m180420_145439_user_send_manager_message
 */
class m180420_145439_user_send_manager_message extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'send_manager_message', $this->integer()->defaultValue(0));
        $this->update('{{%user}}', ['send_manager_message' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'send_manager_message');
    }
}
