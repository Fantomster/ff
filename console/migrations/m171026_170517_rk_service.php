<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170517_rk_service extends Migration
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
            '{{%rk_service}}',
            [
                'id'=> $this->primaryKey(11),
                'org'=> $this->integer(11)->null()->defaultValue(null),
                'fd'=> $this->datetime()->null()->defaultValue(null),
                'td'=> $this->datetime()->null()->defaultValue(null),
                'status_id'=> $this->integer(11)->null()->defaultValue(null),
                'is_deleted'=> $this->integer(11)->null()->defaultValue(null),
                'object_id'=> $this->string(45)->null()->defaultValue(null),
                'user_id'=> $this->integer(11)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
                'code'=> $this->string(128)->null()->defaultValue(null),
                'name'=> $this->string(255)->null()->defaultValue(null),
                'address'=> $this->string(255)->null()->defaultValue(null),
                'phone'=> $this->string(45)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_service}}');
    }
}
