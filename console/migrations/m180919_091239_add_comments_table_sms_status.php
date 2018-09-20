<?php

use yii\db\Migration;

class m180919_091239_add_comments_table_sms_status extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `sms_status` comment "Таблица сведений о статусах отправки СМС";');
        $this->addCommentOnColumn('{{%sms_status}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%sms_status}}', 'status','Идентификатор статуса отправки СМС');
        $this->addCommentOnColumn('{{%sms_status}}', 'text','Наименование статуса отправки СМС');
    }

    public function safeDown()
    {
        $this->execute('alter table `sms_status` comment "";');
        $this->dropCommentFromColumn('{{%sms_status}}', 'id');
        $this->dropCommentFromColumn('{{%sms_status}}', 'status');
        $this->dropCommentFromColumn('{{%sms_status}}', 'text');
    }
}
