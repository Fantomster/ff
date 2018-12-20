<?php

use yii\db\Migration;

class m181214_115355_add_comments_fields_assorti12 extends Migration
{
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%order}}', 'is_recadv_sent', 'Показатель состояния отправки файла recadv (0 - не отправлен, 1 - отправлен)');
        $this->addCommentOnColumn('{{%user}}', 'subscribe','Показатель состояния наличия согласия на E-mail рассылки (0 - не согласен, 1 - согласен)');
        $this->addCommentOnColumn('{{%user}}', 'send_manager_message','Показатель состояния согласия на получение технических сообщений от менеджера (для маркетинга) (0 - не согласен, 1 - согласен)');
        $this->addCommentOnColumn('{{%user}}', 'send_week_message','Показатель состояния согласия на получение еженедельных сообщений от менеджеров (0 - не согласен, 1 - согласен)');
        $this->addCommentOnColumn('{{%user}}', 'send_demo_message','Показатель состояния согласия на получение демонстрационных сообщений от менеджеров (0 - не согласен, 1 - согласен)');
        $this->addCommentOnColumn('{{%user}}', 'sms_subscribe','Показатель состояния наличия согласия на sms рассылки (0 - не согласен, 1 - согласен)');
        $this->addCommentOnColumn('{{%sms_error}}', 'error_code','Код ошибки при отправке СМС-сообщения');
        $this->addCommentOnColumn('{{%sms_error}}', 'sms_send_id','Идентификатор пользователя-отправителя СМС-сообщения');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%order}}', 'is_recadv_sent');
        $this->dropCommentFromColumn('{{%user}}', 'subscribe');
        $this->dropCommentFromColumn('{{%user}}', 'send_manager_message');
        $this->dropCommentFromColumn('{{%user}}', 'send_week_message');
        $this->dropCommentFromColumn('{{%user}}', 'send_demo_message');
        $this->dropCommentFromColumn('{{%user}}', 'sms_subscribe');
        $this->dropCommentFromColumn('{{%sms_error}}', 'error_code');
        $this->dropCommentFromColumn('{{%sms_error}}', 'sms_send_id');
    }
}
