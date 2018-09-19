<?php

use yii\db\Migration;

class m180919_085247_add_comments_table_all_service_type extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `all_service_type` comment "Таблица сведений о типах учётных сервисов";');
        $this->addCommentOnColumn('{{%all_service_type}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%all_service_type}}', 'denom', 'Наименование типа сервисов');
        $this->addCommentOnColumn('{{%all_service_type}}', 'is_active', 'Показатель состояния активности типа сервисов (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%all_service_type}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%all_service_type}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `all_service_type` comment "";');
        $this->dropCommentFromColumn('{{%all_service_type}}', 'id');
        $this->dropCommentFromColumn('{{%all_service_type}}', 'denom');
        $this->dropCommentFromColumn('{{%all_service_type}}', 'is_active');
        $this->dropCommentFromColumn('{{%all_service_type}}', 'created_at');
        $this->dropCommentFromColumn('{{%all_service_type}}', 'updated_at');
    }
}
