<?php

use yii\db\Migration;

class m180629_084746_add_comments_table_role extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `role` comment "Таблица сведений о ролях пользователей в системе";');
        $this->addCommentOnColumn('{{%role}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%role}}', 'name', 'Наименование роли пользователя в системе');
        $this->addCommentOnColumn('{{%role}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%role}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%role}}', 'can_admin', 'Показатель возможности роли исполнять функции администратора в системе (0 - не может, 1  - может)');
        $this->addCommentOnColumn('{{%role}}', 'can_manage', 'Показатель возможности роли исполнять функции менеджера в системе (0 - не может, 1  - может)');
        $this->addCommentOnColumn('{{%role}}', 'organization_type', 'Идентификатор типа организации (1 - ресторан, 2 - поставщик, 3 - ?)');
        $this->addCommentOnColumn('{{%role}}', 'can_observe', 'Показатель возможности роли исполнять функции наблюдателя в системе (0 - не может, 1  - может)');
    }

    public function safeDown()
    {
        $this->execute('alter table `role` comment "";');
        $this->dropCommentFromColumn('{{%role}}', 'id');
        $this->dropCommentFromColumn('{{%role}}', 'name');
        $this->dropCommentFromColumn('{{%role}}', 'created_at');
        $this->dropCommentFromColumn('{{%role}}', 'updated_at');
        $this->dropCommentFromColumn('{{%role}}', 'can_admin');
        $this->dropCommentFromColumn('{{%role}}', 'can_manage');
        $this->dropCommentFromColumn('{{%role}}', 'organization_type');
        $this->dropCommentFromColumn('{{%role}}', 'can_observe');
    }
}
