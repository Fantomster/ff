<?php

use yii\db\Migration;

class m180731_101012_add_comments_table_iiko_store extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_store` comment "Таблица сведений о складах в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_store}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_store}}', 'uuid', 'Уникальный системный идентификатор склада в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_store}}', 'org_id', 'Идентификатор организации, к которой относится склад');
        $this->addCommentOnColumn('{{%iiko_store}}', 'denom', 'Наименование склада в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_store}}', 'is_active', 'Показатель состояния активности склада в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_store}}', 'store_code', 'Видимый пользователю идентификатор склада в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_store}}', 'store_type', 'Тип склада в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_store}}', 'comment', 'Комментарий');
        $this->addCommentOnColumn('{{%iiko_store}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%iiko_store}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_store` comment "";');
        $this->dropCommentFromColumn('{{%iiko_store}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_store}}', 'uuid');
        $this->dropCommentFromColumn('{{%iiko_store}}', 'org_id');
        $this->dropCommentFromColumn('{{%iiko_store}}', 'denom');
        $this->dropCommentFromColumn('{{%iiko_store}}', 'is_active');
        $this->dropCommentFromColumn('{{%iiko_store}}', 'store_code');
        $this->dropCommentFromColumn('{{%iiko_store}}', 'store_type');
        $this->dropCommentFromColumn('{{%iiko_store}}', 'comment');
        $this->dropCommentFromColumn('{{%iiko_store}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_store}}', 'updated_at');
    }
}
