<?php

use yii\db\Migration;

class m190215_105024_add_comments_table_preorder extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `preorder` comment "Таблица сведений о предзаказах";');
        $this->addCommentOnColumn('{{%preorder}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%preorder}}', 'organization_id','Идентификатор организации-клиента, создавшей предзаказ');
        $this->addCommentOnColumn('{{%preorder}}', 'user_id','Идентификатор пользователя, создавшего предзаказ');
        $this->addCommentOnColumn('{{%preorder}}', 'is_active','Показатель состояния активности предзаказа (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%preorder}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%preorder}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `preorder` comment "";');
        $this->dropCommentFromColumn('{{%preorder}}', 'id');
        $this->dropCommentFromColumn('{{%preorder}}', 'organization_id');
        $this->dropCommentFromColumn('{{%preorder}}', 'user_id');
        $this->dropCommentFromColumn('{{%preorder}}', 'is_active');
        $this->dropCommentFromColumn('{{%preorder}}', 'created_at');
        $this->dropCommentFromColumn('{{%preorder}}', 'updated_at');
    }
}
