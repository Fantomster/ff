<?php

use yii\db\Migration;

class m180919_085632_add_comments_table_web_api_log extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `web_api_log` comment "Таблица сведений о логах web api";');
        $this->addCommentOnColumn('{{%web_api_log}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%web_api_log}}', 'url','URL-адрес страницы, на которую был отправлен запрос');
        $this->addCommentOnColumn('{{%web_api_log}}', 'request','Запрос');
        $this->addCommentOnColumn('{{%web_api_log}}', 'response','Ответ');
        $this->addCommentOnColumn('{{%web_api_log}}', 'user_id','Идентификатор пользователя, осуществившего запрос');
        $this->addCommentOnColumn('{{%web_api_log}}', 'organization_id','Идентификатор организации, в которой работает пользователь');
        $this->addCommentOnColumn('{{%web_api_log}}', 'type','Тип сообщения (success, error)');
        $this->addCommentOnColumn('{{%web_api_log}}', 'request_at','Дата и время совершения запроса');
        $this->addCommentOnColumn('{{%web_api_log}}', 'response_at','Дата и время отправления ответа');
        $this->addCommentOnColumn('{{%web_api_log}}', 'guide','Уникальный идентификатор запроса');
        $this->addCommentOnColumn('{{%web_api_log}}', 'ip','IP-адрес пользователя, осуществившего запрос');
    }

    public function safeDown()
    {
        $this->execute('alter table `web_api_log` comment "";');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'id');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'url');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'request');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'response');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'user_id');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'organization_id');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'type');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'request_at');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'response_at');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'guide');
        $this->dropCommentFromColumn('{{%web_api_log}}', 'ip');
    }
}
