<?php

use yii\db\Migration;

/**
 * Class m180420_075026_user_subscribe
 */
class m180420_075026_user_subscribe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'subscribe', $this->integer()->null()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'subscribe');
    }
}
