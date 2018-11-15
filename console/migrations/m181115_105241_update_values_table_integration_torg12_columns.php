<?php

use yii\db\Migration;

class m181115_105241_update_values_table_integration_torg12_columns extends Migration
{
    public function safeUp()
    {
        $this->update('{{%integration_torg12_columns}}', array(
            'value' => 'цена с ндс, руб.|цена с ндс руб.|цена, руб.|цена руб.'),
            'id=9'
        );
        $this->update('{{%integration_torg12_columns}}', array(
            'value' => 'сумма.*с.*ндс.*|стоимость.*товаров.*с.*налогом.*|Сумма с.*|сумма с учетом ндс, руб. коп.'),
            'id=10'
        );
    }

    public function safeDown()
    {
        $this->update('{{%integration_torg12_columns}}', array(
            'value' => 'цена с ндс, руб.|цена с ндс руб.|цена, руб.|цена руб.|сумма с учетом ндс, руб. коп.'),
            'id=9'
        );
        $this->update('{{%integration_torg12_columns}}', array(
            'value' => 'сумма.*с.*ндс.*|стоимость.*товаров.*с.*налогом.*|Сумма с.*'),
            'id=10'
        );
    }
}
