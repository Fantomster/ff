<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170521_rk_tasks extends Migration
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
            '{{%rk_tasks}}',
            [
                'id'=> $this->primaryKey(11),
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'acc'=> $this->integer(11)->null()->defaultValue(null),
                'tasktype_id'=> $this->integer(11)->null()->defaultValue(null),
                'guid'=> $this->string(45)->null()->defaultValue(null),
                'intstatus_id'=> $this->integer(11)->null()->defaultValue(null),
                'wsstatus_id'=> $this->integer(11)->null()->defaultValue(null),
                'wsclientstatus_id'=> $this->integer(11)->null()->defaultValue(null),
                'fd'=> $this->datetime()->null()->defaultValue(null),
                'td'=> $this->datetime()->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
                'callback_at'=> $this->datetime()->null()->defaultValue(null),
                'isactive'=> $this->integer(11)->null()->defaultValue(null),
                'retry'=> $this->integer(11)->null()->defaultValue(null),
                'fcode'=> $this->string(45)->null()->defaultValue(null),
                'version'=> $this->string(45)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_tasks}}');
    }
}
