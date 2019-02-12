<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vetis_transport}}`.
 */
class m190211_144600_create_vetis_transport_table extends Migration
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
        $this->createTable('{{%vetis_transport}}', [
            'id'                    => $this->primaryKey(),
            'org_id'                => $this->integer(11)->notNull()->comment('ИД организации'),
            'vehicle_number'        => $this->string(255)->null()->comment('Номер машины'),
            'trailer_number'        => $this->string(255)->null()->comment('Номер полуприцепа'),
            'container_number'      => $this->string(255)->null()->comment('Номер контейнера'),
            'trasport_storage_type' => $this->smallInteger(4)->null()->comment('Способ хранения'),
        ]);

        $this->createIndex('idx_org_id_vetis_transport', '{{%vetis_transport}}', 'org_id');
        $this->createIndex('idx_guid_vetis_ingredients', '{{%vetis_ingredients}}', 'guid');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%vetis_transport}}');
        $this->dropIndex('idx_org_id_vetis_transport', '{{%vetis_transport}}');
        $this->dropIndex('idx_guid_vetis_ingredients', '{{%vetis_ingredients}}');
    }
}
