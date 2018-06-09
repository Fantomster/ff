<?php

use yii\db\Migration;

class m180608_134301_add_inn2 extends Migration
{

    public $tableName = '{{%integration_torg12_columns}}';

    public function safeUp()
    {
        $this->insert($this->tableName, [
            'name' => 'postav',
            'value' => 'Поставщик',
            'regular_expression' => 0
        ]);
        $this->insert($this->tableName, [
            'name' => 'inn_kpp_prodav',
            'value' => 'ИНН/КПП продавца:',
            'regular_expression' => 0
        ]);
    }

    public function safeDown()
    {
        $this->delete($this->tableName, ['name' => 'postav']);
        $this->delete($this->tableName, ['name' => 'inn_kpp_prodav']);
    }

}
