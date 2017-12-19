<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170511_rk_category extends Migration
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
            '{{%rk_category}}',
            [
                'id'=> $this->primaryKey(11),
                'rid'=> $this->integer(11)->null()->defaultValue(null),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'up'=> $this->integer(11)->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
                'org_id'=> $this->integer(11)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_category}}');
    }
}
