<?php

use yii\db\Migration;

class m180928_121145_add_comments_table_waybill_content extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {

        $this->execute('alter table `waybill_content` comment "Таблица сведений о товарных позициях приходных накладных";');
        $this->addCommentOnColumn('{{%waybill_content}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%waybill_content}}', 'waybill_id','Идентификатор приходной накладной');
        $this->addCommentOnColumn('{{%waybill_content}}', 'order_content_id','Идентификатор товарной позиции в заказе');
        $this->addCommentOnColumn('{{%waybill_content}}', 'product_outer_id','Идентификатор продукта во внешней учётной системе');
        $this->addCommentOnColumn('{{%waybill_content}}', 'quantity_waybill','Количество товара в приходной накладной');
        $this->addCommentOnColumn('{{%waybill_content}}', 'vat_waybill','Ставка НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'merc_uuid','Универсальный идентификатор товара в системе ВЕТИС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'unload_status','Статус для выгрузки товара в накладную (0 - не выгружать, 1 - выгружать)');
        $this->addCommentOnColumn('{{%waybill_content}}', 'sum_with_vat','Стоимость товара с НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'sum_without_vat','Стоимость товара без НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'price_with_vat','Цена товара с НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'price_without_vat','Цена товара без НДС');
    }

    public function safeDown()
    {
        $this->execute('alter table `waybill_content` comment "";');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'id');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'waybill_id');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'order_content_id');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'product_outer_id');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'quantity_waybill');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'vat_waybill');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'merc_uuid');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'unload_status');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'sum_with_vat');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'sum_without_vat');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'price_with_vat');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'price_without_vat');
    }
}
