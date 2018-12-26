<?php

use yii\db\Migration;

class m181226_103513_add_comments_table_waybill extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `waybill` comment "Таблица сведений о приходных накладных";');
        $this->addCommentOnColumn('{{%waybill}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%waybill}}', 'acquirer_id', 'Идентификатор организации-ресторана');
        $this->addCommentOnColumn('{{%waybill}}', 'status_id', 'Идентификатор статуса приходной накладной (1 - сопоставлена, 2 - сформирована, 3 - ошибка, 4 - сброшена, 5 - выгружена, 6 - выгружается)');
        $this->addCommentOnColumn('{{%waybill}}', 'service_id', 'Идентификатор учётного сервиса (1 - R-Keeper, 2 - IIKO, 8 - 1С, 10 - Tillypad)');
        $this->addCommentOnColumn('{{%waybill}}', 'outer_number_code', 'Номер документа по приходной накладной');
        $this->addCommentOnColumn('{{%waybill}}', 'outer_number_additional', 'Дополнительное поле для номера документа');
        $this->addCommentOnColumn('{{%waybill}}', 'outer_store_id', 'Идентификатор склада');
        $this->addCommentOnColumn('{{%waybill}}', 'outer_note', 'Примечание к приходной накладной');
        $this->addCommentOnColumn('{{%waybill}}', 'outer_order_date', 'Дата из заказа, по которому создана приходная накладная');
        $this->addCommentOnColumn('{{%waybill}}', 'outer_agent_id', 'Идентификатор контрагента в учётной системе');
        $this->addCommentOnColumn('{{%waybill}}', 'vat_included', 'Показатель состояния учёта включения НДС в цену (0 - не включает, 1 - включает)');
        $this->addCommentOnColumn('{{%waybill}}', 'doc_date', 'Дата и время создания приходной накладной');
        $this->addCommentOnColumn('{{%waybill}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%waybill}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%waybill}}', 'exported_at', 'Дата и время выгрузки приходной накладной');
        $this->addCommentOnColumn('{{%waybill}}', 'payment_delay', 'Отсрочка платежа в днях по данной приходной накладной');
        $this->addCommentOnColumn('{{%waybill}}', 'payment_delay_date', 'Дата и время отсрочки платежа');
        $this->addCommentOnColumn('{{%waybill}}', 'outer_document_id', 'Поле для записи номера документа, который создан во внешней учётной системе в случае успешной выгрузки');
    }

    public function safeDown()
    {
        $this->execute('alter table `waybill` comment "";');
        $this->dropCommentFromColumn('{{%waybill}}', 'id');
        $this->dropCommentFromColumn('{{%waybill}}', 'acquirer_id');
        $this->dropCommentFromColumn('{{%waybill}}', 'status_id');
        $this->dropCommentFromColumn('{{%waybill}}', 'service_id');
        $this->dropCommentFromColumn('{{%waybill}}', 'outer_number_code');
        $this->dropCommentFromColumn('{{%waybill}}', 'outer_number_additional');
        $this->dropCommentFromColumn('{{%waybill}}', 'outer_store_id');
        $this->dropCommentFromColumn('{{%waybill}}', 'outer_note');
        $this->dropCommentFromColumn('{{%waybill}}', 'outer_order_date');
        $this->dropCommentFromColumn('{{%waybill}}', 'outer_agent_id');
        $this->dropCommentFromColumn('{{%waybill}}', 'vat_included');
        $this->dropCommentFromColumn('{{%waybill}}', 'doc_date');
        $this->dropCommentFromColumn('{{%waybill}}', 'created_at');
        $this->dropCommentFromColumn('{{%waybill}}', 'updated_at');
        $this->dropCommentFromColumn('{{%waybill}}', 'exported_at');
        $this->dropCommentFromColumn('{{%waybill}}', 'payment_delay');
        $this->dropCommentFromColumn('{{%waybill}}', 'payment_delay_date');
        $this->dropCommentFromColumn('{{%waybill}}', 'outer_document_id');
    }
}
