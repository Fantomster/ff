<?php

use yii\db\Migration;

class m181115_155800_add_comments_table_email_fails extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `email_fails` comment "Таблица сведений о неудачах отправки электронных писем";');
        $this->addCommentOnColumn('{{%email_fails}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%email_fails}}', 'type','Тип неудачи отправки электронного письма (1 - техническая неисправность, 2 - жалоба на письмо)');
        $this->addCommentOnColumn('{{%email_fails}}', 'email','Е-мэйл, на который не удалось отправить электронное письмо');
        $this->addCommentOnColumn('{{%email_fails}}', 'body','Содержание электронного письма, которое не удалось отправить');
        $this->addCommentOnColumn('{{%email_fails}}', 'created_at','Дата и время создания записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `email_fails` comment "";');
        $this->dropCommentFromColumn('{{%email_fails}}', 'id');
        $this->dropCommentFromColumn('{{%email_fails}}', 'type');
        $this->dropCommentFromColumn('{{%email_fails}}', 'email');
        $this->dropCommentFromColumn('{{%email_fails}}', 'body');
        $this->dropCommentFromColumn('{{%email_fails}}', 'created_at');
    }
}