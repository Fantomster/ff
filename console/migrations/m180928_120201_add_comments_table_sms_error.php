<?php

use yii\db\Migration;

class m180928_120201_add_comments_table_sms_error extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `sms_error` comment "Таблица сведений об ошибках при отправке СМС-сообщений";');
        $this->addCommentOnColumn('{{%sms_error}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%sms_error}}', 'date','Дата и время отправления СМС-сообщения');
        $this->addCommentOnColumn('{{%sms_error}}', 'message','Текст СМС-сообщения');
        $this->addCommentOnColumn('{{%sms_error}}', 'target','Номер телефона, на который отправлялось СМС-сообщение');
        $this->addCommentOnColumn('{{%sms_error}}', 'error','Описание ошибки отправки СМС-сообщения');
    }

    public function safeDown()
    {
        $this->execute('alter table `sms_error` comment "";');
        $this->dropCommentFromColumn('{{%sms_error}}', 'id');
        $this->dropCommentFromColumn('{{%sms_error}}', 'date');
        $this->dropCommentFromColumn('{{%sms_error}}', 'message');
        $this->dropCommentFromColumn('{{%sms_error}}', 'target');
        $this->dropCommentFromColumn('{{%sms_error}}', 'error');
    }
}
