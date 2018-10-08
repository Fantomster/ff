<?php

use yii\db\Migration;

class m180919_084911_add_comments_table_all_service extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `all_service` comment "Таблица сведений об учётных сервисах";');
        $this->addCommentOnColumn('{{%all_service}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%all_service}}', 'type_id','Тип учётного сервиса');
        $this->addCommentOnColumn('{{%all_service}}', 'is_active','Показатель состояния активности учётного сервиса (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%all_service}}', 'denom','Наименование учётного сервиса');
        $this->addCommentOnColumn('{{%all_service}}', 'vendor','Название организации, которой принадлежит учётная система');
        $this->addCommentOnColumn('{{%all_service}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%all_service}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%all_service}}', 'log_table','Наименование таблицы лога учётного сервиса');
        $this->addCommentOnColumn('{{%all_service}}', 'log_field','Наименование поля, где хранится идентификатор запроса в учётной системе');
    }

    public function safeDown()
    {
        $this->execute('alter table `all_service` comment "";');
        $this->dropCommentFromColumn('{{%all_service}}', 'id');
        $this->dropCommentFromColumn('{{%all_service}}', 'type_id');
        $this->dropCommentFromColumn('{{%all_service}}', 'is_active');
        $this->dropCommentFromColumn('{{%all_service}}', 'denom');
        $this->dropCommentFromColumn('{{%all_service}}', 'vendor');
        $this->dropCommentFromColumn('{{%all_service}}', 'created_at');
        $this->dropCommentFromColumn('{{%all_service}}', 'updated_at');
        $this->dropCommentFromColumn('{{%all_service}}', 'log_table');
        $this->dropCommentFromColumn('{{%all_service}}', 'log_field');
    }
}
