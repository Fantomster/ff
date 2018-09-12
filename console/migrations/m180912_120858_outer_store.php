<?php

use yii\db\Schema;
use yii\db\Migration;

class m180912_120858_outer_store extends Migration
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
            '{{%outer_store}}',
            [
                'id'=> $this->primaryKey(11),
                'outer_uid'=> $this->string(45)->notNull(),
                'service_id'=> $this->smallInteger(4)->notNull(),
                'org_id'=> $this->integer(11)->notNull(),
                'name'=> $this->string(45)->notNull(),
                'is_deleted'=> $this->smallInteger(4)->null()->defaultValue(0),
                'created_at'=> $this->timestamp()->null(),
                'updated_at'=> $this->timestamp()->null()->defaultValue(null),
                'store_type'=> $this->smallInteger(4)->null()->defaultValue(null),
                'tree'=> $this->integer(11)->null()->defaultValue(null),
                'left'=> $this->integer(11)->null()->defaultValue(null),
                'right'=> $this->integer(11)->null()->defaultValue(null),
                'level'=> $this->smallInteger()->null()->defaultValue(null),
                'selected'=> $this->smallInteger(4)->null()->defaultValue(0),
                'collapsed'=> $this->smallInteger(4)->null()->defaultValue(1),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%outer_store}}');
    }
}
