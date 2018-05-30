<?php

use yii\db\Migration;

/**
 * Class m180525_145101_add_columns_to_mercLog
 */
class m180525_145101_add_columns_to_mercLog extends Migration
{
    public $tableName = '{{%merc_dicconst}}';

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
        $this->addColumn('{{%merc_log}}', 'request', $this->text());
        $this->addColumn('{{%merc_log}}', 'response', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%merc_log}}', 'request');
        $this->addColumn('{{%merc_log}}', 'response');
    }
}
