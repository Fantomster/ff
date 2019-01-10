<?php

use yii\db\Migration;

class m190110_110408_add_comments_table_api_session extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `api_session` comment "Таблица сведений о сессиях в API 1-й версии";');
        $this->addCommentOnColumn('{{%api_session}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%api_session}}', 'fid', 'Идентификатор сессии в API 1-й версии');
        $this->addCommentOnColumn('{{%api_session}}', 'token', 'Токен');
        $this->addCommentOnColumn('{{%api_session}}', 'acc', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%api_session}}', 'nonce', 'Специальное слово сессии');
        $this->addCommentOnColumn('{{%api_session}}', 'ip', 'ip-адрес, с которого была проведена сессия в API 1-й версии (не используется)');
        $this->addCommentOnColumn('{{%api_session}}', 'fd', 'Дата и время начала сессии в API 1-й версии');
        $this->addCommentOnColumn('{{%api_session}}', 'td', 'Дата и время окончания сессии в API 1-й версии');
        $this->addCommentOnColumn('{{%api_session}}', 'ver', 'Номер версии реализации сессии');
        $this->addCommentOnColumn('{{%api_session}}', 'status', 'Показатель состояния активности сессии (0 - не активна, 1  - активна)');
        $this->addCommentOnColumn('{{%api_session}}', 'extimefrom', 'Ответ сервера, содержащий время на стороне сервера в API 1-й версии (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `api_session` comment "";');
        $this->dropCommentFromColumn('{{%api_session}}', 'id');
        $this->dropCommentFromColumn('{{%api_session}}', 'fid');
        $this->dropCommentFromColumn('{{%api_session}}', 'token');
        $this->dropCommentFromColumn('{{%api_session}}', 'acc');
        $this->dropCommentFromColumn('{{%api_session}}', 'nonce');
        $this->dropCommentFromColumn('{{%api_session}}', 'ip');
        $this->dropCommentFromColumn('{{%api_session}}', 'fd');
        $this->dropCommentFromColumn('{{%api_session}}', 'td');
        $this->dropCommentFromColumn('{{%api_session}}', 'ver');
        $this->dropCommentFromColumn('{{%api_session}}', 'status');
        $this->dropCommentFromColumn('{{%api_session}}', 'extimefrom');
    }
}
