<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170529_user extends Migration
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
            '{{%user}}',
            [
                'id'=> $this->primaryKey(11),
                'role_id'=> $this->integer(11)->notNull(),
                'status'=> $this->smallInteger(6)->notNull(),
                'email'=> $this->string(255)->null()->defaultValue(null),
                'username'=> $this->string(255)->null()->defaultValue(null),
                'password'=> $this->string(255)->null()->defaultValue(null),
                'auth_key'=> $this->string(255)->null()->defaultValue(null),
                'access_token'=> $this->string(255)->null()->defaultValue(null),
                'logged_in_ip'=> $this->string(255)->null()->defaultValue(null),
                'logged_in_at'=> $this->timestamp()->null()->defaultValue(null),
                'created_ip'=> $this->string(255)->null()->defaultValue(null),
                'created_at'=> $this->timestamp()->null()->defaultValue(null),
                'updated_at'=> $this->timestamp()->null()->defaultValue(null),
                'banned_at'=> $this->timestamp()->null()->defaultValue(null),
                'banned_reason'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('user_email','{{%user}}',['email'],true);
        $this->createIndex('user_username','{{%user}}',['username'],true);
        $this->createIndex('user_role_id','{{%user}}',['role_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_email', '{{%user}}');
        $this->dropIndex('user_username', '{{%user}}');
        $this->dropIndex('user_role_id', '{{%user}}');
        $this->dropTable('{{%user}}');
    }
}
