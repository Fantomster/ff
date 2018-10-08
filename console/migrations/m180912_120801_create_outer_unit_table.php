<?php

use yii\db\Migration;

/**
 * Handles the creation of table `outer_unit`.
 */
class m180912_120801_create_outer_unit_table extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%outer_unit}}',
            [
                'id' => $this->primaryKey(11),
                'outer_uid' => $this->string(45)->null()->comment('Внешний уникальный ID'),
                'service_id' => $this->tinyInteger()->null()->comment('ID Сервиса'),
                'name' => $this->string()->null()->comment('Название продукта'),
                'iso_code' => $this->string(12)->null()->comment('ISO код'),
                'is_deleted' => $this->tinyInteger()->null()->comment('Статус удаления'),
                'created_at' => $this->timestamp()->null()->comment('Дата создания'),
                'updated_at' => $this->timestamp()->null()->comment('Дата обновления'),
            ], $tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%outer_unit}}');
    }
}
