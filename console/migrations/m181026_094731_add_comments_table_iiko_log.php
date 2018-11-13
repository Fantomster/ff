<?php

use yii\db\Migration;

class m181026_094731_add_comments_table_iiko_log extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_log` comment "Таблица сведений о логах в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_log}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_log}}', 'operation_code', 'Код операции, по которой следует запрос в систему');
        $this->addCommentOnColumn('{{%iiko_log}}', 'request', 'Запрос в систему');
        $this->addCommentOnColumn('{{%iiko_log}}', 'response', 'Ответ от системы на запрос');
        $this->addCommentOnColumn('{{%iiko_log}}', 'user_id', 'Идентификатор пользователя, осуществившего запрос в систему');
        $this->addCommentOnColumn('{{%iiko_log}}', 'organization_id', 'Идентификатор организации, в которой работает пользователь, осуществивший запрос в систему');
        $this->addCommentOnColumn('{{%iiko_log}}', 'type', 'Тип успешности запроса в систему (error - запрос вернул ошибку, success - запрос завершился успешно)');
        $this->addCommentOnColumn('{{%iiko_log}}', 'request_at', 'Дата и время отправки запроса в систему');
        $this->addCommentOnColumn('{{%iiko_log}}', 'response_at', 'Дата и время получения ответа на запрос от системы');
        $this->addCommentOnColumn('{{%iiko_log}}', 'guide', 'Уникальный идентификатор запроса');
        $this->addCommentOnColumn('{{%iiko_log}}', 'ip', 'IP-адрес, с которого был осуществлён запрос в систему');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_log` comment "";');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'operation_code');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'request');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'response');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'user_id');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'organization_id');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'type');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'request_at');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'response_at');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'guide');
        $this->dropCommentFromColumn('{{%iiko_log}}', 'ip');
    }
}
