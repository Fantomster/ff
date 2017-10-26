<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170509_rk_actions extends Migration
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
            '{{%rk_actions}}',
            [
                'id'=> $this->primaryKey(11),
                'action'=> $this->string(120)->null()->defaultValue(null),
                'session'=> $this->integer(11)->null()->defaultValue(null),
                'created'=> $this->timestamp()->null()->defaultValue(null),
                'result'=> $this->integer(11)->null()->defaultValue(null),
                'ip'=> $this->string(45)->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_actions}}');
    }
}
