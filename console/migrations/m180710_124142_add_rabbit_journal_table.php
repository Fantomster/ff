<?php

use yii\db\Migration;

/**
 * Class m180710_124142_add_rabbit_journal_table
 */
class m180710_124142_add_rabbit_journal_table extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('rabbit_journal', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer(),
            'action' => $this->string(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'total_count' => $this->integer(),
            'success_count' => $this->integer(),
            'fail_count' => $this->integer(),
            'fail_content' => $this->text(),
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('rabbit_journal');

    }

}
