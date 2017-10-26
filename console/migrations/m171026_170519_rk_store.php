<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170519_rk_store extends Migration
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
            '{{%rk_store}}',
            [
                'id'=> $this->primaryKey(11),
                'acc'=> $this->integer(11)->null()->defaultValue(null),
                'rid'=> $this->integer(11)->null()->defaultValue(null),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'store_type'=> $this->string(128)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
                'is_active'=> $this->integer(11)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_store}}');
    }
}
