<?php

use yii\db\Migration;

/**
 * Class m181224_141253_add_comments_intecration_setting_change
 */
class m181224_141253_add_comments_intecration_setting_change extends Migration
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
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'org_id', 'Указатель на ID организации');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'integration_setting_id', 'Указатель на ID сервиса интеграции');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'old_value', 'Старое значение настройки');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'new_value', 'Новое значение настройки');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'changed_user_id', 'Указатель на ID пользователя который запросил изменения');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'confirmed_user_id', 'Указатель на ID пользователя который подтвердил изменения');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'is_active', 'Активность настройки 1-активна, 0-не активна');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'created_at', 'Дата создания запроса на изменения настройки');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'updated_at', 'Дата последнего изменения');
        $this->addCommentOnColumn('{{%integration_setting_change}}', 'confirmed_at', 'Дата подтвержения настройки');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
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
    }
}
