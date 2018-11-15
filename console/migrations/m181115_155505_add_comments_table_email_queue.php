<?php

use yii\db\Migration;

class m181115_155505_add_comments_table_email_queue extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `email_queue` comment "Таблица сведений об электронных письмах";');
        $this->addCommentOnColumn('{{%email_queue}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%email_queue}}', 'to','Е-мэйл получателя электронного письма (кому)');
        $this->addCommentOnColumn('{{%email_queue}}', 'from','Е-мэйл отправителя электронного письма (от кого)');
        $this->addCommentOnColumn('{{%email_queue}}', 'subject','Заголовок электронного письма');
        $this->addCommentOnColumn('{{%email_queue}}', 'body','Содержание электронного письма');
        $this->addCommentOnColumn('{{%email_queue}}', 'order_id','Идентификатор заказа, по которому отправлено письмо');
        $this->addCommentOnColumn('{{%email_queue}}', 'message_id','Идентификатор очереди на Amazon');
        $this->addCommentOnColumn('{{%email_queue}}', 'status','Статус электронного письма (0 - новое, 1 - отправлено, 2 - получено, 3 - отправить не удалось, неудача)');
        $this->addCommentOnColumn('{{%email_queue}}', 'email_fail_id','Идентификатор неудачи при отправке электронного письма');
        $this->addCommentOnColumn('{{%email_queue}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%email_queue}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `email_queue` comment "";');
        $this->dropCommentFromColumn('{{%email_queue}}', 'id');
        $this->dropCommentFromColumn('{{%email_queue}}', 'to');
        $this->dropCommentFromColumn('{{%email_queue}}', 'from');
        $this->dropCommentFromColumn('{{%email_queue}}', 'subject');
        $this->dropCommentFromColumn('{{%email_queue}}', 'body');
        $this->dropCommentFromColumn('{{%email_queue}}', 'order_id');
        $this->dropCommentFromColumn('{{%email_queue}}', 'message_id');
        $this->dropCommentFromColumn('{{%email_queue}}', 'status');
        $this->dropCommentFromColumn('{{%email_queue}}', 'email_fail_id');
        $this->dropCommentFromColumn('{{%email_queue}}', 'created_at');
        $this->dropCommentFromColumn('{{%email_queue}}', 'updated_at');
    }
}