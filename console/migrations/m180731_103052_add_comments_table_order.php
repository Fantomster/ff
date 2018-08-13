<?php

use yii\db\Migration;

class m180731_103052_add_comments_table_order extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `order` comment "Таблица сведений о заказах";');
        $this->addCommentOnColumn('{{%order}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%order}}', 'client_id','Идентификатор клиента, оформившего заказ');
        $this->addCommentOnColumn('{{%order}}', 'vendor_id','Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%order}}', 'created_by_id','Идентификатор пользователя, создавшего заказ');
        $this->addCommentOnColumn('{{%order}}', 'accepted_by_id','Идентификатор пользователя, завершившего заказ');
        $this->addCommentOnColumn('{{%order}}', 'status','Идентификатор статуса заказа');
        $this->addCommentOnColumn('{{%order}}', 'total_price','Сумма заказа без НДС');
        $this->addCommentOnColumn('{{%order}}', 'invoice_relation','Идентификатор накладной ТОРГ-12');
        $this->addCommentOnColumn('{{%order}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%order}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%order}}', 'requested_delivery','Ожидаемые дата и время доставки товаров');
        $this->addCommentOnColumn('{{%order}}', 'actual_delivery','Реальные дата и время доставки товаров');
        $this->addCommentOnColumn('{{%order}}', 'comment','Комментарий (не используется)');
        $this->addCommentOnColumn('{{%order}}', 'discount','Размер скидки в процентах');
        $this->addCommentOnColumn('{{%order}}', 'discount_type','Идентификатор типа скидки');
        $this->addCommentOnColumn('{{%order}}', 'currency_id','Идентификатор вида валюты, в которой оформлен заказ');
        $this->addCommentOnColumn('{{%order}}', 'completion_date','Дата и время завершения заказа');
        $this->addCommentOnColumn('{{%order}}', 'waybill_number','Номер приходной накладной');
    }

    public function safeDown()
    {
        $this->execute('alter table `order` comment "";');
        $this->dropCommentFromColumn('{{%order}}', 'id');
        $this->dropCommentFromColumn('{{%order}}', 'client_id');
        $this->dropCommentFromColumn('{{%order}}', 'vendor_id');
        $this->dropCommentFromColumn('{{%order}}', 'created_by_id');
        $this->dropCommentFromColumn('{{%order}}', 'accepted_by_id');
        $this->dropCommentFromColumn('{{%order}}', 'status');
        $this->dropCommentFromColumn('{{%order}}', 'total_price');
        $this->dropCommentFromColumn('{{%order}}', 'invoice_relation');
        $this->dropCommentFromColumn('{{%order}}', 'created_at');
        $this->dropCommentFromColumn('{{%order}}', 'updated_at');
        $this->dropCommentFromColumn('{{%order}}', 'requested_delivery');
        $this->dropCommentFromColumn('{{%order}}', 'actual_delivery');
        $this->dropCommentFromColumn('{{%order}}', 'comment');
        $this->dropCommentFromColumn('{{%order}}', 'discount');
        $this->dropCommentFromColumn('{{%order}}', 'discount_type');
        $this->dropCommentFromColumn('{{%order}}', 'currency_id');
        $this->dropCommentFromColumn('{{%order}}', 'completion_date');
        $this->dropCommentFromColumn('{{%order}}', 'waybill_number');
    }
}
