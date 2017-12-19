<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170522_rk_tasktype extends Migration
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
            '{{%rk_tasktype}}',
            [
                'id'=> $this->primaryKey(11),
                'code'=> $this->integer(11)->null()->defaultValue(null),
                'denom'=> $this->string(120)->null()->defaultValue(null),
                'comment'=> $this->string(120)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_tasktype}}');
    }
}
