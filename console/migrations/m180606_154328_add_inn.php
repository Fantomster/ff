<?php

use yii\db\Migration;

/**
 * Class m180606_154328_add_inn
 */
class m180606_154328_add_inn extends Migration
{
    public $tableName = '{{%integration_torg12_columns}}';

    public function safeUp()
    {
        $this->insert($this->tableName, [
            'name' => 'inn',
            'value' => 'ИНН',
            'regular_expression' => 0
        ]);
        $this->insert($this->tableName, [
            'name' => 'name_postav',
            'value' => 'Поставщик|Продавец:',
            'regular_expression' => 1
        ]);
    }

    public function safeDown()
    {
        $this->delete($this->tableName, ['name' => 'inn']);
        $this->delete($this->tableName, ['name' => 'name_postav']);

    }
}