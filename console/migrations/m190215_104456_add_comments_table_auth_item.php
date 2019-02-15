<?php

use yii\db\Migration;

class m190215_104456_add_comments_table_auth_item extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `auth_item` comment "Таблица сведений о ролях с правами доступа";');
        $this->addCommentOnColumn('{{%auth_item}}', 'id','Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%auth_item}}', 'name', 'Название роли с правами доступа на английском языке');
        $this->addCommentOnColumn('{{%auth_item}}', 'type','Тип элемента (1 - роль, 2 - право доступа)');
        $this->addCommentOnColumn('{{%auth_item}}', 'description','Описание роли с правами доступа на русском языке');
        $this->addCommentOnColumn('{{%auth_item}}', 'rule_name','Название права доступа на английском языке');
        $this->addCommentOnColumn('{{%auth_item}}', 'data','Блок данных роли с правами доступа');
        $this->addCommentOnColumn('{{%auth_item}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%auth_item}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `auth_item` comment "";');
        $this->dropCommentFromColumn('{{%auth_item}}', 'id');
        $this->dropCommentFromColumn('{{%auth_item}}', 'name');
        $this->dropCommentFromColumn('{{%auth_item}}', 'type');
        $this->dropCommentFromColumn('{{%auth_item}}', 'description');
        $this->dropCommentFromColumn('{{%auth_item}}', 'rule_name');
        $this->dropCommentFromColumn('{{%auth_item}}', 'data');
        $this->dropCommentFromColumn('{{%auth_item}}', 'created_at');
        $this->dropCommentFromColumn('{{%auth_item}}', 'updated_at');
    }
}
