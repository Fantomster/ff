<?php

use yii\db\Migration;

/**
 * Handles the creation of table `index_email_for_additional_email`.
 */
class m181121_122210_create_index_email_for_additional_email_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx_email_additional_email', '{{%additional_email}}', 'email');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_email_additional_email', '{{%additional_email}}');
    }
}
