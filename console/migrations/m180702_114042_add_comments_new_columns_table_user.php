<?php

use yii\db\Migration;

class m180702_114042_add_comments_new_columns_table_user extends Migration
{
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%user}}', 'subscribe', 'Показатель состояния наличия согласия на E-mail рассылки');
        $this->addCommentOnColumn('{{%user}}', 'sms_subscribe', 'Показатель состояния наличия согласия на sms рассылки');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%user}}', 'subscribe');
        $this->dropCommentFromColumn('{{%user}}', 'sms_subscribe');
    }

}
