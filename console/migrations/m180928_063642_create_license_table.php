<?php

use yii\db\Migration;

/**
 * Handles the creation of table `license`.
 */
class m180928_063642_create_license_table extends Migration
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
        $this->createTable('license', [
            'id' => $this->primaryKey()->comment('Уникальный ID'),
            'name' => $this->string(255)->null()->comment('Наименование лицензии'),
            'is_active' => $this->boolean()->comment('Флаг активности'),
            'created_at' => $this->timestamp()->null()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->null()->comment('Дата обновления')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('license');
    }
}
