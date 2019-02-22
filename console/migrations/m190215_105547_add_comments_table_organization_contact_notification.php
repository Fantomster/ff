<?php

use yii\db\Migration;

class m190215_105547_add_comments_table_organization_contact_notification extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `organization_contact_notification` comment "Таблица сведений о подписке на уведомления организаций об определённых событиях";');
        $this->addCommentOnColumn('{{%organization_contact_notification}}', 'organization_contact_id', 'Идентификатор контакта организации');
        $this->addCommentOnColumn('{{%organization_contact_notification}}', 'client_id','Идентификатор организации');
        $this->addCommentOnColumn('{{%organization_contact_notification}}', 'order_created','Показатель статуса необходимости отправлять уведомление о новом заказе на контакт организации (0 - не отправлять, 1 - отправлять)');
        $this->addCommentOnColumn('{{%organization_contact_notification}}', 'order_canceled','Показатель статуса необходимости отправлять уведомление об отмене заказа на контакт организации (0 - не отправлять, 1 - отправлять)');
        $this->addCommentOnColumn('{{%organization_contact_notification}}', 'order_changed','Показатель статуса необходимости отправлять уведомление об изменениях в заказе на контакт организации (0 - не отправлять, 1 - отправлять)');
        $this->addCommentOnColumn('{{%organization_contact_notification}}', 'order_done','Показатель статуса необходимости отправлять уведомление о завершении заказа на контакт организации (0 - не отправлять, 1 - отправлять)');
        $this->addCommentOnColumn('{{%organization_contact_notification}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%organization_contact_notification}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `organization_contact_notification` comment "";');
        $this->dropCommentFromColumn('{{%organization_contact_notification}}', 'organization_contact_id');
        $this->dropCommentFromColumn('{{%organization_contact_notification}}', 'client_id');
        $this->dropCommentFromColumn('{{%organization_contact_notification}}', 'order_created');
        $this->dropCommentFromColumn('{{%organization_contact_notification}}', 'order_canceled');
        $this->dropCommentFromColumn('{{%organization_contact_notification}}', 'order_changed');
        $this->dropCommentFromColumn('{{%organization_contact_notification}}', 'order_done');
        $this->dropCommentFromColumn('{{%organization_contact_notification}}', 'created_at');
        $this->dropCommentFromColumn('{{%organization_contact_notification}}', 'updated_at');
    }
}
