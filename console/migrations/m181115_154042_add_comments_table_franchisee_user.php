<?php

use yii\db\Migration;

class m181115_154042_add_comments_table_franchisee_user extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `franchisee_user` comment "Таблица сведений о связях пользователей и франчайзи";');
        $this->addCommentOnColumn('{{%franchisee_user}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%franchisee_user}}', 'user_id','Идентификатор пользователя');
        $this->addCommentOnColumn('{{%franchisee_user}}', 'franchisee_id','Идентификатор франчайзи');
    }

    public function safeDown()
    {
        $this->execute('alter table `franchisee_user` comment "";');
        $this->dropCommentFromColumn('{{%franchisee_user}}', 'id');
        $this->dropCommentFromColumn('{{%franchisee_user}}', 'user_id');
        $this->dropCommentFromColumn('{{%franchisee_user}}', 'franchisee_id');
    }
}