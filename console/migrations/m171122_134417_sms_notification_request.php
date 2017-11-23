<?php

use yii\db\Migration;

/**
 * Class m171122_134417_sms_notification_request
 */
class m171122_134417_sms_notification_request extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        //Уведомления для пользователя
        $this->addColumn('sms_notification', 'request_accept', $this->integer(1)->null()->defaultValue(0));
        $this->addColumn('email_notification', 'request_accept', $this->integer(1)->null()->defaultValue(1));
        //Уведомления для дополнительного емайла
        $this->addColumn('additional_email', 'request_accept', $this->integer(1)->null()->defaultValue(0));
        //Пользователь ресторана, создавший заявку
        $this->addColumn('request', 'rest_user_id', $this->integer()->null());
        //Пользователь поставщика, оставивший отклик
        $this->addColumn('request_callback', 'supp_user_id', $this->integer()->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //Уведомления для пользователя
        $this->dropColumn('sms_notification', 'request_accept');
        $this->dropColumn('email_notification', 'request_accept');
        //Уведомления для дополнительного емайла
        $this->dropColumn('additional_email', 'request_accept');
        $this->dropColumn('request', 'rest_user_id');
        $this->dropColumn('request_callback', 'supp_user_id');
    }
}
