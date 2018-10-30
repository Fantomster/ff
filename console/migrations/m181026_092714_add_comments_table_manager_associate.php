<?php

use yii\db\Migration;

class m181026_092714_add_comments_table_manager_associate extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `manager_associate` comment "Таблица сведений о связях пользователей с ролью Руководитель у организаций";');
        $this->addCommentOnColumn('{{%manager_associate}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%manager_associate}}', 'manager_id','Идентификатор пользователя с ролью Руководитель');
        $this->addCommentOnColumn('{{%manager_associate}}', 'organization_id','Идентификатор организации');
    }

    public function safeDown()
    {
        $this->execute('alter table `manager_associate` comment "";');
        $this->dropCommentFromColumn('{{%manager_associate}}', 'id');
        $this->dropCommentFromColumn('{{%manager_associate}}', 'manager_id');
        $this->dropCommentFromColumn('{{%manager_associate}}', 'organization_id');
    }
}
