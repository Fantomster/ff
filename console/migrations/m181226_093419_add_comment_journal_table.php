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
        $this->addCommentOnColumn('{{%journal}}', 'service_id', 'Указатель на ID сервиса интеграции');
        $this->addCommentOnColumn('{{%journal}}', 'operation_code', 'Код операции');
        $this->addCommentOnColumn('{{%journal}}', 'user_id', 'Указатель на ID пользователя выполнившего операцию');
        $this->addCommentOnColumn('{{%journal}}', 'organization_id', 'Указатель на ID организации пользователя ');
        $this->addCommentOnColumn('{{%journal}}', 'response', 'Результат операции');
        $this->addCommentOnColumn('{{%journal}}', 'log_guide', 'Уникальный индентификатор записи в логе');
        $this->addCommentOnColumn('{{%journal}}', 'type', 'Тип операции');
        $this->addCommentOnColumn('{{%journal}}', 'created_at', 'Дата выполнения операции');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%journal}}', 'service_id');
        $this->dropCommentFromColumn('{{%journal}}', 'operation_code');
        $this->dropCommentFromColumn('{{%journal}}', 'user_id');
        $this->dropCommentFromColumn('{{%journal}}', 'organization_id');
        $this->dropCommentFromColumn('{{%journal}}', 'response');
        $this->dropCommentFromColumn('{{%journal}}', 'log_guide');
        $this->dropCommentFromColumn('{{%journal}}', 'type');
        $this->dropCommentFromColumn('{{%journal}}', 'created_at');
    }
}
