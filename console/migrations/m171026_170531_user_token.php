<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170531_user_token extends Migration
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
            '{{%user_token}}',
            [
                'id'=> $this->primaryKey(11),
                'user_id'=> $this->integer(11)->null()->defaultValue(null),
                'type'=> $this->smallInteger(6)->notNull(),
                'token'=> $this->string(255)->notNull(),
                'data'=> $this->string(255)->null()->defaultValue(null),
                'created_at'=> $this->timestamp()->null()->defaultValue(null),
                'expired_at'=> $this->timestamp()->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('user_token_token','{{%user_token}}',['token'],true);
        $this->createIndex('user_token_user_id','{{%user_token}}',['user_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_token_token', '{{%user_token}}');
        $this->dropIndex('user_token_user_id', '{{%user_token}}');
        $this->dropTable('{{%user_token}}');
    }
}
