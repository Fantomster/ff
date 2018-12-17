<?php

use yii\db\Migration;

class m181214_114634_add_comments_table_agent_request extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `agent_request` comment "Таблица сведений о заявках организаций на присоединение к франшизе";');
        $this->addCommentOnColumn('{{%agent_request}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%agent_request}}', 'agent_id','Идентификатор пользователя-франчайзи, сотрудничающего с организацией');
        $this->addCommentOnColumn('{{%agent_request}}', 'target_email','Электронный ящик организации, от которой поступила заявка на присоединение к франшизе');
        $this->addCommentOnColumn('{{%agent_request}}', 'comment','Комментарий франчайзи о заявке');
        $this->addCommentOnColumn('{{%agent_request}}', 'is_processed','Показатель статуса состояния заявки на присоединение организации к франшизе (0 - не в процессе, 1 - в процессе)');
        $this->addCommentOnColumn('{{%agent_request}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%agent_request}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `agent_request` comment "";');
        $this->dropCommentFromColumn('{{%agent_request}}', 'id');
        $this->dropCommentFromColumn('{{%agent_request}}', 'agent_id');
        $this->dropCommentFromColumn('{{%agent_request}}', 'target_email');
        $this->dropCommentFromColumn('{{%agent_request}}', 'comment');
        $this->dropCommentFromColumn('{{%agent_request}}', 'is_processed');
        $this->dropCommentFromColumn('{{%agent_request}}', 'created_at');
        $this->dropCommentFromColumn('{{%agent_request}}', 'updated_at');
    }
}
