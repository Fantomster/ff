<?php

use yii\db\Migration;

class m181226_103740_add_comments_table_api_access extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `api_access` comment "Таблица сведений о доступе к API 1-й версии";');
        $this->addCommentOnColumn('{{%api_access}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%api_access}}', 'fid', 'Идентификатор объекта в таблице');
        $this->addCommentOnColumn('{{%api_access}}', 'org', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%api_access}}', 'login', 'Логин агрегатора подключения к API');
        $this->addCommentOnColumn('{{%api_access}}', 'password', 'Пароль агрегатора подключения к API');
        $this->addCommentOnColumn('{{%api_access}}', 'fd', 'Дата и время начала действия лицензии');
        $this->addCommentOnColumn('{{%api_access}}', 'td', 'Дата и время окончания действия лицензии');
        $this->addCommentOnColumn('{{%api_access}}', 'ver', 'Версия реализации');
        $this->addCommentOnColumn('{{%api_access}}', 'locked', 'Показатель состояния блокировки доступа (0 - не заблокировано, 1  - заблокировано)');
        $this->addCommentOnColumn('{{%api_access}}', 'is_active', 'Показатель состояния активности (0 - не активно, 1 - активно)');
    }

    public function safeDown()
    {
        $this->execute('alter table `api_access` comment "";');
        $this->dropCommentFromColumn('{{%api_access}}', 'id');
        $this->dropCommentFromColumn('{{%api_access}}', 'fid');
        $this->dropCommentFromColumn('{{%api_access}}', 'org');
        $this->dropCommentFromColumn('{{%api_access}}', 'login');
        $this->dropCommentFromColumn('{{%api_access}}', 'password');
        $this->dropCommentFromColumn('{{%api_access}}', 'fd');
        $this->dropCommentFromColumn('{{%api_access}}', 'td');
        $this->dropCommentFromColumn('{{%api_access}}', 'ver');
        $this->dropCommentFromColumn('{{%api_access}}', 'locked');
        $this->dropCommentFromColumn('{{%api_access}}', 'is_active');
    }
}
