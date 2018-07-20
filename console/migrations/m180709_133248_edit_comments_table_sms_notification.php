<?php

use yii\db\Migration;

class m180709_133248_edit_comments_table_sms_notification extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `sms_notification` comment "Таблица сведений о подписке на sms-уведомления пользователей об определённых событиях";');
    }

    public function safeDown()
    {
        $this->execute('alter table `sms_notification` comment "";');
    }
}
