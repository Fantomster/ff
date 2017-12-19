<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170527_rk_wserror extends Migration
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
            '{{%rk_wserror}}',
            [
                'id'=> $this->primaryKey(11),
                'code'=> $this->integer(11)->null()->defaultValue(null),
                'egroup'=> $this->string(45)->null()->defaultValue(null),
                'en_text'=> $this->string(255)->null()->defaultValue(null),
                'denom'=> $this->text()->null()->defaultValue(null),
                'comment'=> $this->string(120)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_wserror}}');
    }
}
