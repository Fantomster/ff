<?php

use yii\db\Migration;

/**
 * Class m180912_115057_create_table_outer_product
 */
class m180912_115057_create_table_outer_product extends Migration
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
            '{{%outer_product}}',
            [
                'id'=> $this->primaryKey(11),
                'service_id'=> $this->tinyInteger()->null()->comment('ID Сервиса'),
                'org_id'=> $this->integer(11)->null()->comment('ID организации'),
                'outer_uid'=> $this->string(45)->null()->comment('Внешний уникальный ID'),
                'name' => $this->string(45)->null()->comment('Название продукта'),
                'parent_uid' => $this->string(45)->null()->comment('Внешний уникальный ID родителя'),
                'level' => $this->tinyInteger()->null()->comment('Уровень'),
                'is_deleted' => $this->tinyInteger()->null()->comment('Статус удаления'),
                'is_category' => $this->tinyInteger()->null()->comment('Статус категории'),
                'outer_unit_id' => $this->integer()->null()->comment('Связь с таблицей outer_unit'),
                'comment' => $this->string()->null()->comment('Комментарий'),
                'created_at' => $this->timestamp()->null()->comment('Дата создания'),
                'updated_at' => $this->timestamp()->null()->comment('Дата обновления'),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%outer_product}}');
    }
}
