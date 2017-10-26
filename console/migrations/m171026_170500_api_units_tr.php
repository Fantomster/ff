<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170500_api_units_tr extends Migration
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
            '{{%api_units_tr}}',
            [
                'id'=> $this->primaryKey(11),
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'lang'=> $this->integer(11)->null()->defaultValue(null),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'comment'=> $this->string(120)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('fid','{{%api_units_tr}}',['fid'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('fid', '{{%api_units_tr}}');
        $this->dropTable('{{%api_units_tr}}');
    }
}
