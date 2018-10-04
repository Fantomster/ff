<?php

use yii\db\Migration;

class m180928_115816_add_comments_table_sms_send extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `sms_send` comment "Таблица сведений об отправленных СМС";');
        $this->addCommentOnColumn('{{%sms_send}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%sms_send}}', 'sms_id','Идентификатор СМС');
        $this->addCommentOnColumn('{{%sms_send}}', 'status_id','Идентификатор статуса отправки СМС');
        $this->addCommentOnColumn('{{%sms_send}}', 'text','Текст СМС');
        $this->addCommentOnColumn('{{%sms_send}}', 'target','Телефонный номер, на который отправлен СМС');
        $this->addCommentOnColumn('{{%sms_send}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%sms_send}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%sms_send}}', 'provider','Провайдер, через который отправлен СМС');
    }

    public function safeDown()
    {
        $this->execute('alter table `sms_send` comment "";');
        $this->dropCommentFromColumn('{{%sms_send}}', 'id');
        $this->dropCommentFromColumn('{{%sms_send}}', 'sms_id');
        $this->dropCommentFromColumn('{{%sms_send}}', 'status_id');
        $this->dropCommentFromColumn('{{%sms_send}}', 'text');
        $this->dropCommentFromColumn('{{%sms_send}}', 'target');
        $this->dropCommentFromColumn('{{%sms_send}}', 'created_at');
        $this->dropCommentFromColumn('{{%sms_send}}', 'updated_at');
        $this->dropCommentFromColumn('{{%sms_send}}', 'provider');
    }
}
