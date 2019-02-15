<?php

use yii\db\Migration;

class m190215_104823_add_comments_table_auth_rule extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `auth_rule` comment "Таблица сведений о правах доступа";');
        $this->addCommentOnColumn('{{%auth_rule}}', 'name', 'Название права доступа');
        $this->addCommentOnColumn('{{%auth_rule}}', 'data','Блок данных права доступа');
        $this->addCommentOnColumn('{{%auth_rule}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%auth_rule}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `auth_rule` comment "";');
        $this->dropCommentFromColumn('{{%auth_rule}}', 'name');
        $this->dropCommentFromColumn('{{%auth_rule}}', 'data');
        $this->dropCommentFromColumn('{{%auth_rule}}', 'created_at');
        $this->dropCommentFromColumn('{{%auth_rule}}', 'updated_at');
    }
}
