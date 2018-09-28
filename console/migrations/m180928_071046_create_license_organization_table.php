<?php

use yii\db\Migration;

/**
 * Handles the creation of table `license_organization`.
 */
class m180928_071046_create_license_organization_table extends Migration
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
        $this->createTable('license_organization', [
            'id' => $this->primaryKey(),
            'license_id' => $this->integer()->comment('Указатель на ID лицензии'),
            'org_id' => $this->integer()->comment('Указатель на организацию'),
            'fd' => $this->timestamp()->comment('Начало действия услуги'),
            'td' => $this->timestamp()->comment('Окончание действия услуги'),
            'created_at' => $this->timestamp()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->comment('Дата обновления'),
            'object_id' => $this->string(64)->comment('(Идентификатор объекта во внешней системе'),
            'outer_user' => $this->string(255)->comment('Имя пользователя во внешней системе'),
            'outer_name' => $this->string(255)->comment('Имя внешнего объекта - название ресторана внутри UCS, например'),
            'outer_address' => $this->string(255)->comment('Адрес внешнего объекта - по данным UCS, например'),
            'outer_phone' => $this->string(32)->comment('Телефон(ы) внешнего объекта лицензии'),
            'outer_last_active' => $this->timestamp()->comment('Время последней зарегистрированной активности'),
            'status_id' => $this->tinyInteger()->comment('Статус лицензии - идентификатор'),
            'is_deleted' => $this->boolean()->comment('Признак soft-delete'),
        ]);

        $this->addForeignKey('license_id_organization', 'license_organization', 'license_id', 'license', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('license_organization');
    }
}
