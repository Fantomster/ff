<?php

use yii\db\Migration;

class m180709_132449_add_comments_table_email_notification extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `email_notification` comment "Таблица сведений о подписке на E-mail-уведомления пользователей об определённых событиях";');
        $this->addCommentOnColumn('{{%email_notification}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%email_notification}}', 'user_id', 'Идентификатор пользователя');
        $this->addCommentOnColumn('{{%email_notification}}', 'rel_user_org_id', 'Идентификатор зависимости пользователей, организаций и ролей');
        $this->addCommentOnColumn('{{%email_notification}}', 'orders', '(Уже не используется)');
        $this->addCommentOnColumn('{{%email_notification}}', 'requests', '(Уже не используется)');
        $this->addCommentOnColumn('{{%email_notification}}', 'changes', '(Уже не используется)');
        $this->addCommentOnColumn('{{%email_notification}}', 'invites', '(Уже не используется)');
        $this->addCommentOnColumn('{{%email_notification}}', 'order_created', 'Показатель статуса необходимости отправлять уведомление о новом заказе на E-mail');
        $this->addCommentOnColumn('{{%email_notification}}', 'order_canceled', 'Показатель статуса необходимости отправлять уведомление об отмене заказа на E-mail');
        $this->addCommentOnColumn('{{%email_notification}}', 'order_changed', 'Показатель статуса необходимости отправлять уведомление об изменениях в заказе на E-mail');
        $this->addCommentOnColumn('{{%email_notification}}', 'order_processing', 'Показатель статуса необходимости отправлять уведомление о начале выполнения заказа на E-mail');
        $this->addCommentOnColumn('{{%email_notification}}', 'order_done', 'Показатель статуса необходимости отправлять уведомление о завершении заказа на E-mail');
        $this->addCommentOnColumn('{{%email_notification}}', 'request_accept', 'Показатель статуса необходимости отправлять уведомление о новых откликах на заявку на E-mail');
        $this->addCommentOnColumn('{{%email_notification}}', 'receive_employee_email', 'Показатель статуса необходимости отправлять уведомления на E-mail поставщиков на E-mail');
        $this->addCommentOnColumn('{{%email_notification}}', 'merc_vsd', 'Показатель статусу необходимости отправлять уведомления о непогашенных ВСД на E-mail ');
    }

    public function safeDown()
    {
        $this->execute('alter table `email_notification` comment "";');
        $this->dropCommentFromColumn('{{%email_notification}}', 'id');
        $this->dropCommentFromColumn('{{%email_notification}}', 'user_id');
        $this->dropCommentFromColumn('{{%email_notification}}', 'rel_user_org_id');
        $this->dropCommentFromColumn('{{%email_notification}}', 'orders');
        $this->dropCommentFromColumn('{{%email_notification}}', 'requests');
        $this->dropCommentFromColumn('{{%email_notification}}', 'changes');
        $this->dropCommentFromColumn('{{%email_notification}}', 'invites');
        $this->dropCommentFromColumn('{{%email_notification}}', 'order_created');
        $this->dropCommentFromColumn('{{%email_notification}}', 'order_canceled');
        $this->dropCommentFromColumn('{{%email_notification}}', 'order_changed');
        $this->dropCommentFromColumn('{{%email_notification}}', 'order_processing');
        $this->dropCommentFromColumn('{{%email_notification}}', 'order_done');
        $this->dropCommentFromColumn('{{%email_notification}}', 'request_accept');
        $this->dropCommentFromColumn('{{%email_notification}}', 'receive_employee_email');
        $this->dropCommentFromColumn('{{%email_notification}}', 'merc_vsd');
    }
}
