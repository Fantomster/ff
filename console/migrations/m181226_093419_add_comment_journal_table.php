<?php

use yii\db\Migration;

/**
 * Class m181226_093419_add_comment_journal_table
 */
class m181226_093419_add_comment_journal_table extends Migration
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
        $this->addCommentOnColumn('{{%journal}}', 'org_id', 'Указатель на ID организации');
        $this->addCommentOnColumn('{{%journal}}', 'integration_setting_id', 'Указатель на ID сервиса интеграции');
        $this->addCommentOnColumn('{{%journal}}', 'old_value', 'Старое значение настройки');
        $this->addCommentOnColumn('{{%journal}}', 'new_value', 'Новое значение настройки');
        $this->addCommentOnColumn('{{%journal}}', 'changed_user_id', 'Указатель на ID пользователя который запросил изменения');
        $this->addCommentOnColumn('{{%journal}}', 'confirmed_user_id', 'Указатель на ID пользователя который подтвердил изменения');
        $this->addCommentOnColumn('{{%journal}}', 'is_active', 'Активность настройки 1-активна, 0-не активна');
        $this->addCommentOnColumn('{{%journal}}', 'created_at', 'Дата создания запроса на изменения настройки');
        $this->addCommentOnColumn('{{%journal}}', 'updated_at', 'Дата последнего изменения');
        $this->addCommentOnColumn('{{%journal}}', 'confirmed_at', 'Дата подтвержения настройки');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%journal}}', 'org_id');
        $this->dropCommentFromColumn('{{%journal}}', 'integration_setting_id');
        $this->dropCommentFromColumn('{{%journal}}', 'old_value');
        $this->dropCommentFromColumn('{{%journal}}', 'new_value');
        $this->dropCommentFromColumn('{{%journal}}', 'changed_user_id');
        $this->dropCommentFromColumn('{{%journal}}', 'confirmed_user_id');
        $this->dropCommentFromColumn('{{%journal}}', 'is_active');
        $this->dropCommentFromColumn('{{%journal}}', 'created_at');
        $this->dropCommentFromColumn('{{%journal}}', 'updated_at');
        $this->dropCommentFromColumn('{{%journal}}', 'confirmed_at');
    }
}
