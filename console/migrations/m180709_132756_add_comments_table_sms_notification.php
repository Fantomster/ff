<?php

use yii\db\Migration;

class m180709_132756_add_comments_table_sms_notification extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `sms_notification` comment "Таблица сведений о подписке на E-mail-уведомления пользователей об определённых событиях";');
        $this->addCommentOnColumn('{{%sms_notification}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%sms_notification}}', 'user_id', 'Идентификатор пользователя');
        $this->addCommentOnColumn('{{%sms_notification}}', 'rel_user_org_id', 'Идентификатор зависимости пользователей, организаций и ролей');
        $this->addCommentOnColumn('{{%sms_notification}}', 'orders', '(Уже не используется)');
        $this->addCommentOnColumn('{{%sms_notification}}', 'requests', '(Уже не используется)');
        $this->addCommentOnColumn('{{%sms_notification}}', 'changes', '(Уже не используется)');
        $this->addCommentOnColumn('{{%sms_notification}}', 'invites', '(Уже не используется)');
        $this->addCommentOnColumn('{{%sms_notification}}', 'order_created', 'Показатель статуса необходимости отправлять уведомление о новом заказе в sms');
        $this->addCommentOnColumn('{{%sms_notification}}', 'order_canceled', 'Показатель статуса необходимости отправлять уведомление об отмене заказа в sms');
        $this->addCommentOnColumn('{{%sms_notification}}', 'order_changed', 'Показатель статуса необходимости отправлять уведомление об изменениях в заказе в sms');
        $this->addCommentOnColumn('{{%sms_notification}}', 'order_processing', 'Показатель статуса необходимости отправлять уведомление о начале выполнения заказа в sms');
        $this->addCommentOnColumn('{{%sms_notification}}', 'order_done', 'Показатель статуса необходимости отправлять уведомление о завершении заказа в sms');
        $this->addCommentOnColumn('{{%sms_notification}}', 'request_accept', 'Показатель статуса необходимости отправлять уведомление о новых откликах на заявку в sms');
        $this->addCommentOnColumn('{{%sms_notification}}', 'receive_employee_sms', 'Показатель статуса необходимости отправлять sms-уведомления поставщикам в sms');
    }

    public function safeDown()
    {
        $this->execute('alter table `sms_notification` comment "";');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'id');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'user_id');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'rel_user_org_id');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'orders');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'requests');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'changes');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'invites');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'order_created');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'order_canceled');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'order_changed');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'order_processing');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'order_done');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'request_accept');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'receive_employee_sms');
    }
}
