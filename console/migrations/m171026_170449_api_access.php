<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170449_api_access extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%api_access}}',
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
        $this->createIndex('fid','{{%api_access}}',['fid'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('fid', '{{%api_access}}');
        $this->dropTable('{{%api_access}}');
    }
}
