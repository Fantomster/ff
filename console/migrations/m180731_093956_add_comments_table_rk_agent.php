<?php

use yii\db\Migration;

class m180731_093956_add_comments_table_rk_agent extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_agent` comment "Таблица сведений о контрагентах в системе R-keeper";');
        $this->addCommentOnColumn('{{%rk_agent}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_agent}}', 'acc', 'Идентификатор организации, связанной с контрагентом');
        $this->addCommentOnColumn('{{%rk_agent}}', 'rid', 'Идентификатор агента в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_agent}}', 'denom', 'Наименование контрагента в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_agent}}', 'agent_type', 'Тип контрагента в системе R_Keeper (1 - внешний, 2 - внутренний, 3  - специальный)');
        $this->addCommentOnColumn('{{%rk_agent}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_agent}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_agent}}', 'comment', 'Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_agent` comment "";');
        $this->dropCommentFromColumn('{{%rk_agent}}', 'id');
        $this->dropCommentFromColumn('{{%rk_agent}}', 'acc');
        $this->dropCommentFromColumn('{{%rk_agent}}', 'rid');
        $this->dropCommentFromColumn('{{%rk_agent}}', 'denom');
        $this->dropCommentFromColumn('{{%rk_agent}}', 'agent_type');
        $this->dropCommentFromColumn('{{%rk_agent}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_agent}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_agent}}', 'comment');
    }
}
