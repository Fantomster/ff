<?php

use yii\db\Migration;

/**
 * Class m180618_153952_add_column_settings_rkws
 */
class m180618_153952_add_column_settings_rkws extends Migration
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
        $this->insert('rk_dicconst', [
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
        $this->delete('rk_dicconst', ['denom' => 'column_number_invoice']);
    }

}
