<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170518_rk_session extends Migration
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
            '{{%rk_session}}',
            [
                'id'=> $this->primaryKey(11),
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'acc'=> $this->integer(11)->null()->defaultValue(null),
                'cook'=> $this->text()->null()->defaultValue(null),
                'rk_sessioncol'=> $this->string(45)->null()->defaultValue(null),
                'ip'=> $this->string(45)->null()->defaultValue(null),
                'fd'=> $this->timestamp()->null()->defaultValue(null),
                'td'=> $this->timestamp()->null()->defaultValue(null),
                'ver'=> $this->integer(11)->null()->defaultValue(null),
                'status'=> $this->integer(11)->null()->defaultValue(null),
                'extime'=> $this->timestamp()->null()->defaultValue(null),
                'comment'=> $this->string(120)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_session}}');
    }
}
