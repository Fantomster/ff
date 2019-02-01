<?php

use yii\db\Migration;

class m190201_103529_add_comments_table_journal extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `journal` comment "Таблица сведений об операциях с сервервами интеграций";');
        $this->addCommentOnColumn('{{%journal}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%journal}}', 'service_id', 'Идентификатор учётного сервиса интеграции');
        $this->addCommentOnColumn('{{%journal}}', 'operation_code', 'Код операции с серверами интеграций');
        $this->addCommentOnColumn('{{%journal}}', 'user_id', 'Идентификатор пользователя, осуществившего запрос на совершение операции');
        $this->addCommentOnColumn('{{%journal}}', 'organization_id', 'Идентификатор организации, сотрудником которой является пользователь, осуществивший запрос на совершение операции');
        $this->addCommentOnColumn('{{%journal}}', 'response', 'Ответ, полученный от сервера интеграции на запрос совершения операции');
        $this->addCommentOnColumn('{{%journal}}', 'log_guide', 'Уникальный идентификатор записи в логе');
        $this->addCommentOnColumn('{{%journal}}', 'type', 'Результат совершения операции (error - ошибка, неудачно, success - успешно)');
        $this->addCommentOnColumn('{{%journal}}', 'created_at', 'Дата и время создания записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `journal` comment "";');
        $this->dropCommentFromColumn('{{%journal}}', 'id');
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
