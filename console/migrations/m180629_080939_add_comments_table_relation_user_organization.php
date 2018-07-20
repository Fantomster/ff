<?php

use yii\db\Migration;

class m180629_080939_add_comments_table_relation_user_organization extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `relation_user_organization` comment "Таблица сведений о зависимостях пользователей, организаций и ролей";');
        $this->addCommentOnColumn('{{%relation_user_organization}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%relation_user_organization}}', 'user_id', 'Идентификатор пользователя');
        $this->addCommentOnColumn('{{%relation_user_organization}}', 'organization_id', 'Идентификатор типа этой организации');
        $this->addCommentOnColumn('{{%relation_user_organization}}', 'role_id', 'Идентификатор роли');
        $this->addCommentOnColumn('{{%relation_user_organization}}', 'is_active', 'Флажок состояния активности зависимости пользователя, организации и роли');
    }

    public function safeDown()
    {
        $this->execute('alter table `relation_user_organization` comment "";');
        $this->dropCommentFromColumn('{{%relation_user_organization}}', 'id');
        $this->dropCommentFromColumn('{{%relation_user_organization}}', 'user_id');
        $this->dropCommentFromColumn('{{%relation_user_organization}}', 'organization_id');
        $this->dropCommentFromColumn('{{%relation_user_organization}}', 'role_id');
        $this->dropCommentFromColumn('{{%relation_user_organization}}', 'is_active');
    }
}
