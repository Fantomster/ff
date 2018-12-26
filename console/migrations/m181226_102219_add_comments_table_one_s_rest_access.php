<?php

use yii\db\Migration;

class m181226_102219_add_comments_table_one_s_rest_access extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_rest_access` comment "Таблица сведений о сессиях доступа к учётной системе 1С";');
        $this->addCommentOnColumn('{{%one_s_rest_access}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_rest_access}}', 'fid', 'Уникальный идентификатор организации в учётной системе 1С');
        $this->addCommentOnColumn('{{%one_s_rest_access}}', 'org', 'Идентификатор организации, чей сотрудник имел сессию доступа к учётной системе 1С');
        $this->addCommentOnColumn('{{%one_s_rest_access}}', 'login', 'Логин агрегатора подключения к 1С');
        $this->addCommentOnColumn('{{%one_s_rest_access}}', 'password', 'Пароль агрегатора подключения к 1С');
        $this->addCommentOnColumn('{{%one_s_rest_access}}', 'fd', 'Дата и время начала сессии доступа к учётной системе 1С');
        $this->addCommentOnColumn('{{%one_s_rest_access}}', 'td', 'Дата и время окончания сессии доступа к учётной системе 1С');
        $this->addCommentOnColumn('{{%one_s_rest_access}}', 'ver', 'Версия модуля доступа к учётной системе 1С');
        $this->addCommentOnColumn('{{%one_s_rest_access}}', 'locked', 'Показатель статуса заблокированности сессии доступа к учётной системе 1С (0 - не заблокирована, 1 - заблокирована)');
        $this->addCommentOnColumn('{{%one_s_rest_access}}', 'is_active', 'Показатель статуса активности сессии доступа к учётной системе 1С (0 - не была активна, 1 - была активна)');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_rest_access` comment "";');
        $this->dropCommentFromColumn('{{%one_s_rest_access}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_rest_access}}', 'fid');
        $this->dropCommentFromColumn('{{%one_s_rest_access}}', 'org');
        $this->dropCommentFromColumn('{{%one_s_rest_access}}', 'login');
        $this->dropCommentFromColumn('{{%one_s_rest_access}}', 'password');
        $this->dropCommentFromColumn('{{%one_s_rest_access}}', 'fd');
        $this->dropCommentFromColumn('{{%one_s_rest_access}}', 'td');
        $this->dropCommentFromColumn('{{%one_s_rest_access}}', 'ver');
        $this->dropCommentFromColumn('{{%one_s_rest_access}}', 'locked');
        $this->dropCommentFromColumn('{{%one_s_rest_access}}', 'is_active');
    }
}
