<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170455_api_lang extends Migration
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
            '{{%api_lang}}',
            [
                'id'=> $this->primaryKey(11),
                'eng_denom'=> $this->string(45)->null()->defaultValue(null),
                'denom'=> $this->string(45)->null()->defaultValue(null),
                'code2'=> $this->string(2)->null()->defaultValue(null),
                'code3'=> $this->string(3)->null()->defaultValue(null),
                'codenum'=> $this->string(3)->null()->defaultValue(null),
                'comment'=> $this->string(45)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%api_lang}}');
    }
}
