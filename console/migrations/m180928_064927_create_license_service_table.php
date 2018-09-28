<?php

use yii\db\Migration;

/**
 * Handles the creation of table `license_service`.
 */
class m180928_064927_create_license_service_table extends Migration
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
        $this->createTable('license_service', [
            'id' => $this->primaryKey()->comment('Уникальный ID'),
            'license_id' => $this->integer()->comment('Указатель на ID лицензии'),
            'service_id' => $this->integer()->comment('Указатель на ID сервиса'),
            'created_at' => $this->timestamp()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->comment('Дата обновления')
        ]);

        $this->addForeignKey('license_id', 'license_service', 'license_id', 'license', 'id', 'CASCADE');
        $this->addForeignKey('service_id', 'license_service', 'service_id', 'all_service', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('license_service');
    }
}
