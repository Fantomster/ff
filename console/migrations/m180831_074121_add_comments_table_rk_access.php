<?php

use yii\db\Migration;

class m180831_074121_add_comments_table_rk_access extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_access` comment "Таблица сведений о доступе к системе R-Keeper";');
        $this->addCommentOnColumn('{{%rk_access}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_access}}', 'fid','Идентификатор объекта в таблице');
        $this->addCommentOnColumn('{{%rk_access}}', 'org','Идентификатор организации');
        $this->addCommentOnColumn('{{%rk_access}}', 'login','Логин агрегатора подключения к R-Keeper');
        $this->addCommentOnColumn('{{%rk_access}}', 'password','Пароль агрегатора подключения к R-Keeper');
        $this->addCommentOnColumn('{{%rk_access}}', 'token','Токен агрегатора подключения к R-Keeper');
        $this->addCommentOnColumn('{{%rk_access}}', 'lic','Ключ лицензии UCS агрегатора подключения к R-Keeper');
        $this->addCommentOnColumn('{{%rk_access}}', 'fd','Дата и время начала действия лицензии');
        $this->addCommentOnColumn('{{%rk_access}}', 'td','Дата и время окончания действия лицензии');
        $this->addCommentOnColumn('{{%rk_access}}', 'ver','Версия реализации');
        $this->addCommentOnColumn('{{%rk_access}}', 'locked','Показатель состояния блокировки доступа (0 - не заблокировано, 1  - заблокировано)');
        $this->addCommentOnColumn('{{%rk_access}}', 'salespoint','Код ресторана (не используется)');
        $this->addCommentOnColumn('{{%rk_access}}', 'usereq','Название клиента (не используется)');
        $this->addCommentOnColumn('{{%rk_access}}', 'comment','Комментарий');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_access` comment "";');
        $this->dropCommentFromColumn('{{%rk_access}}', 'id');
        $this->dropCommentFromColumn('{{%rk_access}}', 'fid');
        $this->dropCommentFromColumn('{{%rk_access}}', 'org');
        $this->dropCommentFromColumn('{{%rk_access}}', 'login');
        $this->dropCommentFromColumn('{{%rk_access}}', 'password');
        $this->dropCommentFromColumn('{{%rk_access}}', 'token');
        $this->dropCommentFromColumn('{{%rk_access}}', 'lic');
        $this->dropCommentFromColumn('{{%rk_access}}', 'fd');
        $this->dropCommentFromColumn('{{%rk_access}}', 'td');
        $this->dropCommentFromColumn('{{%rk_access}}', 'ver');
        $this->dropCommentFromColumn('{{%rk_access}}', 'locked');
        $this->dropCommentFromColumn('{{%rk_access}}', 'salespoint');
        $this->dropCommentFromColumn('{{%rk_access}}', 'usereq');
        $this->dropCommentFromColumn('{{%rk_access}}', 'comment');
    }
}
