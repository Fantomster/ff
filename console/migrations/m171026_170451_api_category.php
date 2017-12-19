<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170451_api_category extends Migration
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
            '{{%api_category}}',
            [
                'id'=> $this->primaryKey(11),
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'fd'=> $this->timestamp()->null()->defaultValue(null),
                'td'=> $this->timestamp()->null()->defaultValue(null),
                'ver'=> $this->integer(11)->null()->defaultValue(null),
                'up'=> $this->integer(11)->null()->defaultValue(null),
                'comment'=> $this->string(120)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('fid','{{%api_category}}',['fid'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('fid', '{{%api_category}}');
        $this->dropTable('{{%api_category}}');
    }
}
