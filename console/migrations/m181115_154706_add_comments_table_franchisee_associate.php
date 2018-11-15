<?php

use yii\db\Migration;

class m181115_154706_add_comments_table_franchisee_associate extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `franchisee_associate` comment "Таблица сведений о связях организаций с франчайзи";');
        $this->addCommentOnColumn('{{%franchisee_associate}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%franchisee_associate}}', 'franchisee_id','Идентификатор франчайзи');
        $this->addCommentOnColumn('{{%franchisee_associate}}', 'organization_id','Идентификатор организации');
        $this->addCommentOnColumn('{{%franchisee_associate}}', 'self_registered','Показатель статуса создания связи организации и франчайзи (0 - связь не создана, 1 - организация зарегистрировалась сама, 2 - организация зарегистрирована через админку)');
        $this->addCommentOnColumn('{{%franchisee_associate}}', 'agent_id','Идентификатор пользователя-агента (users)');
        $this->addCommentOnColumn('{{%franchisee_associate}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%franchisee_associate}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `franchisee_associate` comment "";');
        $this->dropCommentFromColumn('{{%franchisee_associate}}', 'id');
        $this->dropCommentFromColumn('{{%franchisee_associate}}', 'franchisee_id');
        $this->dropCommentFromColumn('{{%franchisee_associate}}', 'organization_id');
        $this->dropCommentFromColumn('{{%franchisee_associate}}', 'self_registered');
        $this->dropCommentFromColumn('{{%franchisee_associate}}', 'agent_id');
        $this->dropCommentFromColumn('{{%franchisee_associate}}', 'created_at');
        $this->dropCommentFromColumn('{{%franchisee_associate}}', 'updated_at');
    }
}