<?php

use yii\db\Migration;

/**
 * Class m181114_182756_add_index_to_journal
 */
class m181114_182756_add_index_to_journal extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx_log_guide_journal', '{{%journal}}', 'log_guide');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_log_guide_journal', '{{%journal}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181114_182756_add_index_to_journal cannot be reverted.\n";

        return false;
    }
    */
}
