<?php

use yii\db\Migration;

class m180928_121936_add_comments_table_relation_manager_leader extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `relation_manager_leader` comment "Таблица сведений о связях руководителей и подчинённых сотрудников организаций";');
        $this->addCommentOnColumn('{{%relation_manager_leader}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%relation_manager_leader}}', 'manager_id','Идентификатор пользователя-руководителя');
        $this->addCommentOnColumn('{{%relation_manager_leader}}', 'leader_id','Идентификатор-пользователя-сотрудника, подчинённого руководителя');
    }

    public function safeDown()
    {
        $this->execute('alter table `relation_manager_leader` comment "";');
        $this->dropCommentFromColumn('{{%relation_manager_leader}}', 'id');
        $this->dropCommentFromColumn('{{%relation_manager_leader}}', 'manager_id');
        $this->dropCommentFromColumn('{{%relation_manager_leader}}', 'leader_id');
    }
}
