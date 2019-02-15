<?php

use yii\db\Migration;

class m190215_104212_add_comments_table_auth_assignment extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `auth_assignment` comment "Таблица сведений о ролях пользователей";');
        $this->addCommentOnColumn('{{%auth_assignment}}', 'item_name', 'Название роли с правами доступа');
        $this->addCommentOnColumn('{{%auth_assignment}}', 'user_id','Идентификатор пользователя, которому назначена данная роль');
        $this->addCommentOnColumn('{{%auth_assignment}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%auth_assignment}}', 'organization_id','Идентификатор организации, чьим сотрудником является данный пользователь');
        $this->addCommentOnColumn('{{%auth_assignment}}', 'id','Идентификатор записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `auth_assignment` comment "";');
        $this->dropCommentFromColumn('{{%auth_assignment}}', 'item_name');
        $this->dropCommentFromColumn('{{%auth_assignment}}', 'user_id');
        $this->dropCommentFromColumn('{{%auth_assignment}}', 'created_at');
        $this->dropCommentFromColumn('{{%auth_assignment}}', 'organization_id');
        $this->dropCommentFromColumn('{{%auth_assignment}}', 'id');
    }
}
