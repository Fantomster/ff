<?php

use yii\db\Migration;

class m190201_103128_add_comments_table_integration_setting_change extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `integration_setting_change` comment "Таблица сведений об изменениях настроек интеграций организаций";');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'org_id', 'Идентификатор организации, чьи настройки изменены');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'integration_setting_id', 'Идентификатор сервиса интеграции, у которого изменены настройки');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'old_value', 'Старое значение настройки');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'new_value', 'Новое значение настройки');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'changed_user_id', 'Идентификатор пользователя, который запросил изменения');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'confirmed_user_id', 'Идентификатор пользователя, который подтвердил изменения');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'is_active', 'Показатель активности настройки интеграции (0 - не активна, 1 - активна)');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'created_at', 'Дата и время создания запроса на изменение настройки интеграции');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'updated_at', 'Дата и время последнего изменения настройки интеграции');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'confirmed_at', 'Дата и время подтверждения изменения настройки');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'rejected_user_id', 'Идентификатор пользователя, который отменил запрос об изменении настройки интеграции');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'rejected_at', 'Дата и время отмены изменения настройки интеграции');
    }

    public function safeDown()
    {
        $this->execute('alter table `integration_setting_change` comment "";');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'id');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'org_id');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'integration_setting_id');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'old_value');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'new_value');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'changed_user_id');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'confirmed_user_id');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'is_active');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'created_at');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'updated_at');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'confirmed_at');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'rejected_user_id');
        $this->dropCommentFromColumn('{{%integration_setting_change}}', 'rejected_at');
    }
}
