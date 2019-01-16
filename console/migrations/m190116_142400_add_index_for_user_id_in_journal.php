<?php

use yii\db\Migration;

/**
 * Class m190116_142400_add_index_for_user_id_in_journal
 */
class m190116_142400_add_index_for_user_id_in_journal extends Migration
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
        $this->createIndex(
            'idx-journal-user_id',
            '{{%journal}}',
            'user_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190116_142400_add_index_for_user_id_in_journal cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190116_142400_add_index_for_user_id_in_journal cannot be reverted.\n";

        return false;
    }
    */
}
