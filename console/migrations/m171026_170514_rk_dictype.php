<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170514_rk_dictype extends Migration
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
            '{{%rk_dictype}}',
            [
                'id'=> $this->primaryKey(11),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
                'contr'=> $this->string(128)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_dictype}}');
    }
}
