<?php

use yii\db\Migration;

class m181019_075648_add_value_table_iiko_dicconst extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->update('{{%iiko_dicconst}}', array(
            'denom' => 'URL_iiko'),
            'id=1'
        );
        $this->insert('{{%iiko_dicconst}}', [
            'denom' => 'URL_tillypad',
            'def_value' => 'http://192.168.100.100:8080/resto/api',
            'comment' => 'Ссылка для подключения к вашему Tillypad (http://192.168.100.100:8080/resto/api)',
            'type' => 2,
            'is_active' => 1
        ]);
    }

    public function safeDown()
    {
        $this->update('{{%iiko_dicconst}}', array(
            'denom' => 'URL'),
            'id=1'
        );
        $this->delete('{{%iiko_dicconst}}', ['id' => 10]);
    }
}
