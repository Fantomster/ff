<?php

use yii\db\Migration;

/**
 * Class m180705_091836_iiko_operations_add
 */
class m180705_091836_iiko_operations_add extends Migration
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
        $this->batchInsert('all_service_operation', ['service_id', 'code', 'denom', 'comment'], [
            ['2', '1', '/auth', 'Авторизация'],
            ['2', '2', '/suppliers/', 'Контрагенты'],
            ['2', '3', '/corporation/stores/', 'Склады'],
            ['2', '4', '/products/', 'Товары, Категории'],
            ['2', '5', '/documents/import/incomingInvoice', 'Отправка накладной'],
            ['2', '9', '/logout', 'Освобождение лицензии'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('all_service_operation', ['service_id' => 2]);
    }
}
