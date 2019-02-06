<?php

use yii\db\Migration;

class m190201_103338_add_comments_table_integration_setting_value extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `integration_setting_value` comment "Таблица сведений о значениях настроек интеграций для организаций";');
        $this->addCommentOnColumn('{{%integration_setting_value}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%integration_setting_value}}', 'setting_id', 'Идентификатор настройки интеграции');
        $this->addCommentOnColumn('{{%integration_setting_value}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%integration_setting_value}}', 'value', 'Значение настройки интеграции');
        $this->addCommentOnColumn('{{%integration_setting_value}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%integration_setting_value}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `integration_setting_value` comment "";');
        $this->dropCommentFromColumn('{{%integration_setting_value}}', 'id');
        $this->dropCommentFromColumn('{{%integration_setting_value}}', 'setting_id');
        $this->dropCommentFromColumn('{{%integration_setting_value}}', 'org_id');
        $this->dropCommentFromColumn('{{%integration_setting_value}}', 'value');
        $this->dropCommentFromColumn('{{%integration_setting_value}}', 'created_at');
        $this->dropCommentFromColumn('{{%integration_setting_value}}', 'updated_at');
    }
}
