<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170515_rk_edism extends Migration
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
            '{{%rk_edism}}',
            [
                'id'=> $this->primaryKey(11),
                'acc'=> $this->integer(11)->null()->defaultValue(null),
                'rid'=> $this->integer(11)->null()->defaultValue(null),
                'denom'=> $this->string(64)->null()->defaultValue(null),
                'ratio'=> $this->decimal(10, 10)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
                'group_rid'=> $this->integer(11)->null()->defaultValue(null),
                'group_name'=> $this->string(127)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_edism}}');
    }
}
