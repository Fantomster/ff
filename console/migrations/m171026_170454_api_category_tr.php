<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170454_api_category_tr extends Migration
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
            '{{%api_category_tr}}',
            [
                'id'=> $this->primaryKey(11),
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'lang'=> $this->integer(11)->null()->defaultValue(null),
                'denom'=> $this->string(255)->null()->defaultValue(null),
                'comment'=> $this->string(45)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('fid','{{%api_category_tr}}',['fid'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('fid', '{{%api_category_tr}}');
        $this->dropTable('{{%api_category_tr}}');
    }
}
