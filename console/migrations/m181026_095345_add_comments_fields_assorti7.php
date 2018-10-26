<?php

use yii\db\Migration;

class m181026_095345_add_comments_fields_assorti7 extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `mp_ed` comment "Таблица сведений о единицах измерения товаров для Маркет Плейс";');
        $this->execute('alter table `integration_invoice` comment "Таблица общих сведений о накладных поставщика ТОРГ-12";');
        $this->addCommentOnColumn('{{%order_content}}', 'product_id','Идентификатор товара в таблице catalog_base_goods');
        $this->addCommentOnColumn('{{%order_content}}', 'invoice_content_id','Идентификатор накладной ТОРГ-12');
        $this->addCommentOnColumn('{{%order}}', 'accepted_at','Дата и время завершения заказа');
        $this->addCommentOnColumn('{{%order}}', 'replaced_order_id','Идентификатор заказа, который был заменён текущим');
        $this->addCommentOnColumn('{{%additional_email}}', 'order_created','Показатель состояния необходимости отправлять оповещения о создании заказов (0 - не отправлять, 1 - отправлять)');
        $this->addCommentOnColumn('{{%additional_email}}', 'order_canceled','Показатель состояния необходимости отправлять оповещения об отмене заказов (0 - не отправлять, 1 - отправлять)');
        $this->addCommentOnColumn('{{%additional_email}}', 'order_changed','Показатель состояния необходимости отправлять оповещения об изменении заказов (0 - не отправлять, 1 - отправлять)');
        $this->addCommentOnColumn('{{%additional_email}}', 'order_processing','Показатель состояния необходимости отправлять оповещения о взятии заказов в работу (0 - не отправлять, 1 - отправлять)');
        $this->addCommentOnColumn('{{%additional_email}}', 'order_done','Показатель состояния необходимости отправлять оповещения о завершении заказов (0 - не отправлять, 1 - отправлять)');
        $this->addCommentOnColumn('{{%additional_email}}', 'merc_vsd','Показатель состояния необходимости отправлять оповещения о непогашенных ВСД (0 - не отправлять, 1 - отправлять)');
        $this->addCommentOnColumn('{{%additional_email}}', 'confirmed','Показатель статуса подтверждения дополнительного е-мэйла (0 - не подтверждён, 1 - подтверждён)');
        $this->addCommentOnColumn('{{%additional_email}}', 'token','Хэш данного е-мэйла');
    }

    public function safeDown()
    {
        $this->execute('alter table `mp_ed` comment "";');
        $this->execute('alter table `integration_invoice` comment "";');
        $this->dropCommentFromColumn('{{%order_content}}', 'product_id');
        $this->dropCommentFromColumn('{{%order_content}}', 'invoice_content_id');
        $this->dropCommentFromColumn('{{%order}}', 'accepted_at');
        $this->dropCommentFromColumn('{{%order}}', 'replaced_order_id');
        $this->dropCommentFromColumn('{{%additional_email}}', 'order_created');
        $this->dropCommentFromColumn('{{%additional_email}}', 'order_canceled');
        $this->dropCommentFromColumn('{{%additional_email}}', 'order_changed');
        $this->dropCommentFromColumn('{{%additional_email}}', 'order_processing');
        $this->dropCommentFromColumn('{{%additional_email}}', 'order_done');
        $this->dropCommentFromColumn('{{%additional_email}}', 'merc_vsd');
        $this->dropCommentFromColumn('{{%additional_email}}', 'confirmed');
        $this->dropCommentFromColumn('{{%additional_email}}', 'token');
    }
}
