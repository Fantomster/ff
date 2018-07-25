<?php

use yii\db\Migration;

class m180712_160949_add_values_table_integration_torg12_columns extends Migration
{
    public $tableName = '{{%integration_torg12_columns}}';

    public function safeUp()
    {
        $this->insert($this->tableName, [
            'name' => 'consignee',
            'value' => 'Грузополучатель|Грузополучатель и его адрес:',
            'regular_expression' => 0
        ]);
    }

    public function safeDown()
    {
        $this->delete($this->tableName, ['name' => 'consignee']);
    }

}
