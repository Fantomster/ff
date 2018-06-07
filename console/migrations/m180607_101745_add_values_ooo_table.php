<?php

use yii\db\Migration;

class m180607_101745_add_values_ooo_table extends Migration
{

    public $tableName = '{{%ooo}}';

    public function safeUp()
    {
        $this->insert($this->tableName, [
            'name_short' => 'АО',
            'name_long' => 'Акционерное общество'
        ]);
        $this->insert($this->tableName, [
            'name_short' => 'ЗАО',
            'name_long' => 'Закрытое акционерное общество'
        ]);
        $this->insert($this->tableName, [
            'name_short' => 'ИП',
            'name_long' => 'Индивидуальный предприниматель'
        ]);
        $this->insert($this->tableName, [
            'name_short' => 'ОАО',
            'name_long' => 'Открытое акционерное общество'
        ]);
        $this->insert($this->tableName, [
            'name_short' => 'ОДО',
            'name_long' => 'Общество с дополнительной ответственностью'
        ]);
        $this->insert($this->tableName, [
            'name_short' => 'ООО',
            'name_long' => 'Общество с ограниченной ответственностью'
        ]);
        $this->insert($this->tableName, [
            'name_short' => 'ПАО',
            'name_long' => 'Публичное акционерное общество'
        ]);
        $this->insert($this->tableName, [
            'name_short' => 'ФГУП',
            'name_long' => 'Федеральное государственное унитарное предприятие'
        ]);
    }

    public function safeDown()
    {
        $this->truncateTable($this->tableName);

    }

}
