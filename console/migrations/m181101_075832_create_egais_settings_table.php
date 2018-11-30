<?php

use yii\db\Migration;

/**
 * Handles the creation of table `egais_settings`.
 */
class m181101_075832_create_egais_settings_table extends Migration
{
    public $tableName = '{{%egais_settings}}';

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
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull()->comment('id ресторана'),
            'egais_url' => $this->string()->notNull()->comment('url по которому нужно стучаться в егаис'),
            'fsrar_id' => $this->string()->notNull()->comment('идентификатор  организации  в  ФС  РАР'),
            'created_at' => $this->datetime()->null()->defaultValue(null)->comment('Дата создания записи'),
            'updated_at' => $this->datetime()->null()->defaultValue(null)->comment('Дата последнего изменения записи'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
