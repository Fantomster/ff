<?php

use yii\db\Migration;

class m181023_093332_add_values_for_tillypad_table_all_service_operation extends Migration
{
    public $tableName = '{{%all_service_operation}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert($this->tableName, [
            'service_id' => '10',
            'code' => '1',
            'denom' => '/auth',
            'comment' => 'Авторизация'
        ]);
        $this->insert($this->tableName, [
            'service_id' => '10',
            'code' => '2',
            'denom' => '/suppliers/',
            'comment' => 'Контрагенты'
        ]);
        $this->insert($this->tableName, [
            'service_id' => '10',
            'code' => '3',
            'denom' => '/corporation/stores/',
            'comment' => 'Склады'
        ]);
        $this->insert($this->tableName, [
            'service_id' => '10',
            'code' => '4',
            'denom' => '/products/',
            'comment' => 'Товары, Категории'
        ]);
        $this->insert($this->tableName, [
            'service_id' => '10',
            'code' => '5',
            'denom' => '/documents/import/incomingInvoice',
            'comment' => 'Отправка накладной'
        ]);
        $this->insert($this->tableName, [
            'service_id' => '10',
            'code' => '9',
            'denom' => '/logout',
            'comment' => 'Освобождение лицензии'
        ]);
    }

    public function safeDown()
    {
        $this->delete($this->tableName, ['service_id' => '10']);
    }
}
