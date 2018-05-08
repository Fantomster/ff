<?php

use yii\db\Migration;

/**
 * Class m180507_171140_add_odinsrest_access_table
 */
class m180507_171140_add_odinsrest_access_table extends Migration
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
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%odinsrest_access}}',
            [
                'id'=> $this->primaryKey(11),
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'org'=> $this->integer(11)->null()->defaultValue(null),
                'login'=> $this->string(255)->null()->defaultValue(null),
                'password'=> $this->string(255)->null()->defaultValue(null),
                'fd'=> $this->timestamp()->null()->defaultValue(null),
                'td'=> $this->timestamp()->null()->defaultValue(null),
                'ver'=> $this->integer(11)->null()->defaultValue(null),
                'locked'=> $this->integer(11)->null()->defaultValue(null),
                'is_active'=> $this->integer(11)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%odinsrest_access}}');
    }

}
