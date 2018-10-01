<?php

use yii\db\Migration;

class m180928_120419_add_comments_table_sms_code_change_mobile extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `sms_code_change_mobile` comment "Таблица сведений о сменах номеров мобильных телефонов пользователей";');
        $this->addCommentOnColumn('{{%sms_code_change_mobile}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%sms_code_change_mobile}}', 'user_id','Идентификатор пользователя, у которого менялся номер мобильного телефона');
        $this->addCommentOnColumn('{{%sms_code_change_mobile}}', 'phone','Номер телефона (новый), на который было отправлено СМС-сообщение');
        $this->addCommentOnColumn('{{%sms_code_change_mobile}}', 'code','Код, который был отправлен на новый номер мобильного телефона');
        $this->addCommentOnColumn('{{%sms_code_change_mobile}}', 'attempt','Количество попыток отправки СМС-сообщений');
        $this->addCommentOnColumn('{{%sms_code_change_mobile}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%sms_code_change_mobile}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `sms_code_change_mobile` comment "";');
        $this->dropCommentFromColumn('{{%sms_code_change_mobile}}', 'id');
        $this->dropCommentFromColumn('{{%sms_code_change_mobile}}', 'user_id');
        $this->dropCommentFromColumn('{{%sms_code_change_mobile}}', 'phone');
        $this->dropCommentFromColumn('{{%sms_code_change_mobile}}', 'code');
        $this->dropCommentFromColumn('{{%sms_code_change_mobile}}', 'attempt');
        $this->dropCommentFromColumn('{{%sms_code_change_mobile}}', 'created_at');
        $this->dropCommentFromColumn('{{%sms_code_change_mobile}}', 'updated_at');
    }
}
