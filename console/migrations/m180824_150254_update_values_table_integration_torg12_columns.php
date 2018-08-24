<?php

use yii\db\Migration;

class m180824_150254_update_values_table_integration_torg12_columns extends Migration
{
    public function safeUp()
    {
        $this->update('{{%integration_torg12_columns}}', array(
            'value' => 'код товара/ работ, услуг|код|isbn|ean|артикул|артикул поставщика|код товара поставщика|код (артикул)|штрих-код|код вида товара'),
            'id=4'
        );
        $this->update('{{%integration_torg12_columns}}', array(
            'value' => 'всего по накладной|всего к оплате'),
            'id=12'
        );
    }

    public function safeDown()
    {
        $this->update('{{%integration_torg12_columns}}', array(
            'value' => 'код товара/ работ, услуг|код|isbn|ean|артикул|артикул поставщика|код товара поставщика|код (артикул)|штрих-код'),
            'id=4'
        );
        $this->update('{{%integration_torg12_columns}}', array(
            'value' => 'всего по накладной'),
            'id=12'
        );
    }
}
