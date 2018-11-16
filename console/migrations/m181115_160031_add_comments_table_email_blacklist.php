<?php

use yii\db\Migration;

class m181115_160031_add_comments_table_email_blacklist extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `email_blacklist` comment "Таблица сведений о е-мэйлах, попавших в чёрный список";');
        $this->addCommentOnColumn('{{%email_blacklist}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%email_blacklist}}', 'email','Е-мэйл, попавший в чёрный список');
        $this->addCommentOnColumn('{{%email_blacklist}}', 'created_at','Дата и время создания записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `email_blacklist` comment "";');
        $this->dropCommentFromColumn('{{%email_blacklist}}', 'id');
        $this->dropCommentFromColumn('{{%email_blacklist}}', 'email');
        $this->dropCommentFromColumn('{{%email_blacklist}}', 'created_at');
    }
}