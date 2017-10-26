<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170523_rk_units extends Migration
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
            '{{%rk_units}}',
            [
                'id'=> $this->primaryKey(11),
                'denom'=> $this->string(128)->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_units}}');
    }
}
