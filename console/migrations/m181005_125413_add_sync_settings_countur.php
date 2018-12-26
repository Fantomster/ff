<?php

/**
 * Class Migration
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-10-05
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

use yii\db\Migration;
use common\helpers\DBNameHelper;

class m181005_125413_add_sync_settings_countur extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {

        $dbName = DBNameHelper::getMainName();

        $this->createTable('{{%integration_setting}}', [
            'id' => $this->primaryKey()->comment('Уникальный идентификатор настройки'),
            'name' => $this->string(255)->notNull()->comment('Наименование настройки'),
            'default_value' => $this->string(255)->null()->comment('Значение по умолчанию'),
            'comment' => $this->string(255)->notNull()->comment('Комментарий - подробное описание настройки, отображается на фронт'),
            'type' => "ENUM('dropdown_list', 'input_text', 'input_number', 'checkbox', 'radio', 'switch', 'password') NOT NULL DEFAULT 'input_text'",
            'is_active' => $this->tinyInteger()->comment('Флаг активности объекта'),
            'item_list' => $this->string()->comment('Список значение по умолчанию в формате JSON, для отображения при начальном выборе, например { 1: "Включено", 2: "Выключено"}'),
        ]);
        $this->addCommentOnColumn('{{%integration_setting}}', 'type', 'Тип настройки - вып. список, полее ввода и т.п.');

        $this->createTable('{{%integration_setting_value}}', [
            'id' => $this->primaryKey()->comment('Уникальный идентификатор'),
            'setting_id' => $this->integer()->notNull()->comment('Указатель на настройку'),
            'org_id' => $this->integer()->notNull()->comment('Указатель на организацию'),
            'value' => $this->string(255)->notNull()->comment('Значение настройки для данной организации'),
            'created_at' => $this->timestamp()->null(),
            'updated_at' => $this->timestamp()->null(),
        ]);

        $this->addForeignKey('{{%integration_setting_value_setting}}', '{{%integration_setting_value}}', 'setting_id', '{{%integration_setting}}', 'id');
        $this->addForeignKey('{{%integration_setting_value_org}}', '{{%integration_setting_value}}', 'org_id', $dbName . '.organization', 'id');

    }

    public function Down()
    {

        $this->dropForeignKey('{{%integration_setting_value_org}}', '{{%integration_setting_value}}');
        $this->dropForeignKey('{{%integration_setting_value_setting}}', '{{%integration_setting_value}}');
        $this->droptable('{{%integration_setting_value}}');
        $this->droptable('{{%integration_setting}}');

    }

}
