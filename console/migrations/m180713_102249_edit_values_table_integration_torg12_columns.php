<?php

use yii\db\Migration;

class m180713_102249_edit_values_table_integration_torg12_columns extends Migration
{
    public function safeUp()
    {
        $this->update('{{%integration_torg12_columns}}', array(
            'regular_expression' => 'Грузополучатель и его адрес:|Грузополучатель'),
            'id=19'
        );
    }

    public function safeDown()
    {
        $this->update('{{%integration_torg12_columns}}', array(
            'regular_expression' => 'Грузополучатель|Грузополучатель и его адрес:'),
            'id=19'
        );
    }
}
