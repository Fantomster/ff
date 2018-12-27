<?php

use yii\db\Migration;

class m181226_153930_delete_table_api_category_eng_v extends Migration
{
    public $tableName = '{{%api_category_eng_v}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->dropTable($this->tableName);
    }

    public function safeDown()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            $this->tableName,
            [
                'fid' => $this->primaryKey(11)->defaultValue(null),
                'denom' => $this->string(255)->null()->defaultValue(null),
                'up' => $this->integer(11)->null()->defaultValue(null)
            ], $tableOptions
        );
    }
}
