<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170508_rk_access extends Migration
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
            '{{%rk_access}}',
            [
                'id'=> $this->primaryKey(11),
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'org'=> $this->integer(11)->null()->defaultValue(null),
                'login'=> $this->string(120)->null()->defaultValue(null),
                'password'=> $this->string(120)->null()->defaultValue(null),
                'token'=> $this->string(120)->null()->defaultValue(null),
                'lic'=> $this->text()->null()->defaultValue(null),
                'fd'=> $this->timestamp()->null()->defaultValue(null),
                'td'=> $this->timestamp()->null()->defaultValue(null),
                'ver'=> $this->integer(11)->null()->defaultValue(null),
                'locked'=> $this->integer(11)->null()->defaultValue(null),
                'salespoint'=> $this->string(45)->null()->defaultValue(null),
                'usereq'=> $this->string(255)->null()->defaultValue(null),
                'comment'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_access}}');
    }
}
