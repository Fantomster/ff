<?php

use yii\db\Migration;

/**
 * Class m180420_143641_user_first_logged_in_at
 */
class m180420_143641_user_first_logged_in_at extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'first_logged_in_at', $this->timestamp()->null()->after('logged_in_at'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'first_logged_in_at');
    }
}
