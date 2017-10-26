<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170456_api_session extends Migration
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
            '{{%api_session}}',
            [
                'id'=> $this->primaryKey(11),
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'token'=> $this->string(255)->null()->defaultValue(null),
                'acc'=> $this->integer(11)->null()->defaultValue(null),
                'nonce'=> $this->string(255)->null()->defaultValue(null),
                'ip'=> $this->string(25)->null()->defaultValue(null),
                'fd'=> $this->timestamp()->null()->defaultValue(null),
                'td'=> $this->timestamp()->null()->defaultValue(null),
                'ver'=> $this->integer(11)->null()->defaultValue(null),
                'status'=> $this->integer(11)->null()->defaultValue(null),
                'extimefrom'=> $this->timestamp()->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('fid','{{%api_session}}',['fid'],false);
        $this->createIndex('tok','{{%api_session}}',['token'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('fid', '{{%api_session}}');
        $this->dropIndex('tok', '{{%api_session}}');
        $this->dropTable('{{%api_session}}');
    }
}
