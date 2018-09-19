<?php

use yii\db\Migration;

class m180919_091522_add_comments_fields_assorti2 extends Migration
{

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%sms_notification}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%sms_notification}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%email_notification}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%email_notification}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%order_content}}', 'merc_uuid','Уникальный идентификатор ВСД');
        $this->addCommentOnColumn('{{%order_content}}', 'vat_product','Ставка НДС');
        $this->addCommentOnColumn('{{%order_content}}', 'edi_desadv','Название файла desadv IDE');
        $this->addCommentOnColumn('{{%order_content}}', 'edi_alcdes','Название файла alcdes IDE');
        $this->addCommentOnColumn('{{%order_content}}', 'edi_recadv','Название файла recadv IDE');
        $this->addCommentOnColumn('{{%order_content}}', 'edi_number','Номер накладной EDI');
        $this->addCommentOnColumn('{{%order_content}}', 'edi_invoice','Номер счёта EDI');
        $this->addCommentOnColumn('{{%order}}', 'service_id','Идентификатор учётного сервиса (all_service)');
        $this->addCommentOnColumn('{{%order}}', 'status_updated_at','Дата и время последнего изменения статуса заказа');
        $this->addCommentOnColumn('{{%order}}', 'edi_order','Идентификатор EDI заказа');
        $this->addCommentOnColumn('{{%order}}', 'edi_ordersp','Название файла ordersp, который поступает от поставщика');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%sms_notification}}', 'created_at');
        $this->dropCommentFromColumn('{{%sms_notification}}', 'updated_at');
        $this->dropCommentFromColumn('{{%email_notification}}', 'created_at');
        $this->dropCommentFromColumn('{{%email_notification}}', 'updated_at');
        $this->dropCommentFromColumn('{{%order_content}}', 'merc_uuid');
        $this->dropCommentFromColumn('{{%order_content}}', 'vat_product');
        $this->dropCommentFromColumn('{{%order_content}}', 'edi_desadv');
        $this->dropCommentFromColumn('{{%order_content}}', 'edi_alcdes');
        $this->dropCommentFromColumn('{{%order_content}}', 'edi_recadv');
        $this->dropCommentFromColumn('{{%order_content}}', 'edi_number');
        $this->dropCommentFromColumn('{{%order_content}}', 'edi_invoice');
        $this->dropCommentFromColumn('{{%order}}', 'service_id');
        $this->dropCommentFromColumn('{{%order}}', 'status_updated_at');
        $this->dropCommentFromColumn('{{%order}}', 'edi_order');
        $this->dropCommentFromColumn('{{%order}}', 'edi_ordersp');
    }
}
