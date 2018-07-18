<?php

use yii\db\Migration;

class m180712_144836_edit_values_table_integration_torg_12_columns extends Migration
{
    public function safeUp()
    {
        $this->update('{{%integration_torg12_columns}}', array(
            'regular_expression' => 'название|наименование|наименование, характеристика, сорт, артикул товара|наименование товара (описание выполненных работ, оказанных услуг), имущественного права|наименование,характеристика, сорт, артикул товара'),
            'id=2'
        );
    }

    public function safeDown()
    {
        $this->update('{{%integration_torg12_columns}}', array(
            'regular_expression' => 'название|наименование|наименование, характеристика, сорт, артикул товара|наименование товара (описание выполненных работ, оказанных услуг), имущественного права'),
            'id=2'
        );
    }
}
