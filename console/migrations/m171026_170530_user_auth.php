<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170530_user_auth extends Migration
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
            '{{%user_auth}}',
            [
                'id'=> $this->primaryKey(11),
                'user_id'=> $this->integer(11)->notNull(),
                'provider'=> $this->string(255)->notNull(),
                'provider_id'=> $this->string(255)->notNull(),
                'provider_attributes'=> $this->text()->notNull(),
                'created_at'=> $this->timestamp()->null()->defaultValue(null),
                'updated_at'=> $this->timestamp()->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('user_auth_provider_id','{{%user_auth}}',['provider_id'],false);
        $this->createIndex('user_auth_user_id','{{%user_auth}}',['user_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_auth_provider_id', '{{%user_auth}}');
        $this->dropIndex('user_auth_user_id', '{{%user_auth}}');
        $this->dropTable('{{%user_auth}}');
    }
}
