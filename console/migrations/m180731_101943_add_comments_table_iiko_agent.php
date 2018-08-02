<?php

use yii\db\Migration;

class m180731_101943_add_comments_table_iiko_agent extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_agent` comment "Таблица сведений о контрагентах в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_agent}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_agent}}', 'uuid', 'Уникальный идентификатор агента в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_agent}}', 'org_id', 'Идентификатор организации, связанной с контрагентом');
        $this->addCommentOnColumn('{{%iiko_agent}}', 'denom', 'Наименование контрагента');
        $this->addCommentOnColumn('{{%iiko_agent}}', 'is_active', 'Показатель статуса активности категории товара в системе IIKO (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%iiko_agent}}', 'comment', 'Комментарий (не используется)');
        $this->addCommentOnColumn('{{%iiko_agent}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%iiko_agent}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%iiko_agent}}', 'vendor_id', 'Идентификатор поставщика (не используется)');
        $this->addCommentOnColumn('{{%iiko_agent}}', 'store_id', 'Идентификатор склада в системе IIKO');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_agent` comment "";');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'uuid');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'org_id');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'denom');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'is_active');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'comment');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'updated_at');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'vendor_id');
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'store_id');
    }
}
