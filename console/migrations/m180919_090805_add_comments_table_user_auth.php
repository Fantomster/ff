<?php

use yii\db\Migration;

class m180919_090805_add_comments_table_user_auth extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `user_auth` comment "Таблица сведений о свойствах аутентификации пользователей";');
        $this->addCommentOnColumn('{{%user_auth}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%user_auth}}', 'user_id','Идентификатор пользователя');
        $this->addCommentOnColumn('{{%user_auth}}', 'provider','Наименование провайдера');
        $this->addCommentOnColumn('{{%user_auth}}', 'provider_id','Идентификатор провайдера');
        $this->addCommentOnColumn('{{%user_auth}}', 'provider_attributes','Атрибуты провайдера');
        $this->addCommentOnColumn('{{%user_auth}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%user_auth}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `user_auth` comment "";');
        $this->dropCommentFromColumn('{{%user_auth}}', 'id');
        $this->dropCommentFromColumn('{{%user_auth}}', 'user_id');
        $this->dropCommentFromColumn('{{%user_auth}}', 'provider');
        $this->dropCommentFromColumn('{{%user_auth}}', 'provider_id');
        $this->dropCommentFromColumn('{{%user_auth}}', 'provider_attributes');
        $this->dropCommentFromColumn('{{%user_auth}}', 'created_at');
        $this->dropCommentFromColumn('{{%user_auth}}', 'updated_at');
    }
}
