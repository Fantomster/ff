<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170512_rk_dic extends Migration
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
            '{{%rk_dic}}',
            [
                'id'=> $this->primaryKey(11),
                'org_id'=> $this->integer(11)->null()->defaultValue(null),
                'dictype_id'=> $this->integer(11)->null()->defaultValue(null),
                'dicstatus_id'=> $this->integer(11)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
                'obj_count'=> $this->integer(11)->null()->defaultValue(null),
                'obj_mapcount'=> $this->integer(11)->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_dic}}');
    }
}
