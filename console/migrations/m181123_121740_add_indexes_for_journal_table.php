<?php

use yii\db\Migration;

/**
 * Class m181123_121740_add_indexes_for_journal_table
 */
class m181123_121740_add_indexes_for_journal_table extends Migration
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
        $this->createIndex('idx_service_id_journal', '{{%journal}}', 'service_id');
        $this->createIndex('idx_organization_id_journal', '{{%journal}}', 'organization_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_service_id_journal', '{{%journal}}');
        $this->dropIndex('idx_organization_id_journal', '{{%journal}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181123_121740_add_indexes_for_journal_table cannot be reverted.\n";

        return false;
    }
    */
}
