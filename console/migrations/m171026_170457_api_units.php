<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170457_api_units extends Migration
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
            '{{%api_units}}',
            [
                'id'=> $this->primaryKey(11),
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'denom'=> $this->string(45)->null()->defaultValue(null),
                'fd'=> $this->timestamp()->null()->defaultValue(null),
                'td'=> $this->timestamp()->null()->defaultValue(null),
                'ver'=> $this->integer(11)->null()->defaultValue(null),
                'comment'=> $this->string(120)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('fid','{{%api_units}}',['fid'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('fid', '{{%api_units}}');
        $this->dropTable('{{%api_units}}');
    }
}
