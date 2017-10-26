<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170506_profile extends Migration
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
            '{{%profile}}',
            [
                'id'=> $this->primaryKey(11),
                'user_id'=> $this->integer(11)->notNull(),
                'created_at'=> $this->timestamp()->null()->defaultValue(null),
                'updated_at'=> $this->timestamp()->null()->defaultValue(null),
                'full_name'=> $this->string(255)->null()->defaultValue(null),
                'timezone'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('profile_user_id','{{%profile}}',['user_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('profile_user_id', '{{%profile}}');
        $this->dropTable('{{%profile}}');
    }
}
