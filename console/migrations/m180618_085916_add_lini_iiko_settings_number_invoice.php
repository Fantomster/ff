<?php

use yii\db\Migration;

/**
 * Class m180618_085916_add_lini_iiko_settings_number_invoice
 */
class m180618_085916_add_lini_iiko_settings_number_invoice extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('iiko_dicconst', [
            'denom' => 'column_number_invoice',
            'def_value' => '1',
            'comment' => 'Отображать № накладной в истории заказов',
            'type' => '1',
            'is_active' => '1',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('iiko_dicconst', ['denom' => 'column_number_invoice']);
    }
}
