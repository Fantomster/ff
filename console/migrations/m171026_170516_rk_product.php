<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170516_rk_product extends Migration
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
            '{{%rk_product}}',
            [
                'id'=> $this->primaryKey(11),
                'acc'=> $this->integer(11)->null()->defaultValue(null),
                'rid'=> $this->integer(11)->null()->defaultValue(null),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'cat_id'=> $this->integer(11)->null()->defaultValue(null),
                'type'=> $this->string(45)->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
                'fproduct_id'=> $this->integer(11)->null()->defaultValue(null),
                'group_rid'=> $this->integer(11)->null()->defaultValue(null),
                'group_name'=> $this->string(128)->null()->defaultValue(null),
                'unit_rid'=> $this->integer(11)->null()->defaultValue(null),
                'unitname'=> $this->string(128)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_product}}');
    }
}
