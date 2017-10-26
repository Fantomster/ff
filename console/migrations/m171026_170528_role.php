<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170528_role extends Migration
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
            '{{%role}}',
            [
                'id'=> $this->primaryKey(11),
                'name'=> $this->string(255)->notNull(),
                'created_at'=> $this->timestamp()->null()->defaultValue(null),
                'updated_at'=> $this->timestamp()->null()->defaultValue(null),
                'can_admin'=> $this->smallInteger(6)->notNull()->defaultValue(0),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%role}}');
    }
}
