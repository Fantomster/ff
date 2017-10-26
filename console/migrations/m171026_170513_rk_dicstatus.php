<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170513_rk_dicstatus extends Migration
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
            '{{%rk_dicstatus}}',
            [
                'id'=> $this->primaryKey(11),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_dicstatus}}');
    }
}
