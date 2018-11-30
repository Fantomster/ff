<?php

use yii\db\Migration;

class m181130_121000_add_comments_table_edi_order_content extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `edi_order_content` comment "Таблица сведений о связях товарных позиций заказов с документами EDI";');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'order_content_id','Идентификатор товарной позиции заказа');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'edi_supplier_article','Артикул товара от поставщика');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'doc_type','Тип документа (0 - заказ, 1 - уведомление об отгрузке DESADV, 2 - уведомление об отгрузке ALCDES)');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'barcode','Штрих-код товара');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'pricewithvat','Цена с НДС');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'taxrate','Ставка НДС');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'uuid','Универсальный идентификатор товара в системе ВЕТИС');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'gtin','Глобальный идентификатор товарной продукции системы ВЕТИС');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'waybill_date','Дата получения товарной накладной по EDI');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'waybill_number','Номер товарной накладной по EDI');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'delivery_note_number','Номер товарно-транспортной накладной по EDI');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'delivery_note_date','Дата получения товарно-транспортной накладной по EDI');
    }

    public function safeDown()
    {
        $this->execute('alter table `edi_order_content` comment "";');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'id');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'order_content_id');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'edi_supplier_article');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'doc_type');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'barcode');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'pricewithvat');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'taxrate');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'uuid');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'gtin');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'waybill_date');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'waybill_number');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'delivery_note_number');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'delivery_note_date');
    }
}
