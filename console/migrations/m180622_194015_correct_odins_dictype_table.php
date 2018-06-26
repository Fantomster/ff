<?php

use yii\db\Migration;

/**
 * Class m180622_194015_correct_odins_dictype_table
 */
class m180622_194015_correct_odins_dictype_table extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->truncateTable('{{%one_s_dictype}}');

        $this->batchInsert('{{%one_s_dictype}}', ['denom', 'comment', 'contr'], [
            ['Контрагенты', 'Синхронизатор контрагентов', 'agent'],
            ['Склады', 'Синхронизатор складов', 'store'],
            ['Товары', 'Синхронизатор товаров', 'goods'],
        ]);

    }

    public function safeDown()
    {
        $this->dropTable('{{%one_s_dictype}}');
    }
}