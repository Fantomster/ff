<?php

use yii\db\Migration;

class m180629_080255_add_comments_table_iiko_waybill extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_waybill` comment "Таблица сведений о приходных накладных в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'agent_uuid', 'Код агента в IIKO');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'org', 'Идентификатор ресторана, в который осуществлена поставка');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'order_id', 'Номер заказа приходной накладной');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'num_code', 'Номер документа по приходной накладной');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'text_code', 'Номер счёта-фактуры');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'readytoexport', 'Показатель состояния готовности к выгрузке приходной накладной (0 – не готова, 1 - готова)');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'status_id', 'Показатель статуса приходной накладной (0 – не сформирована, 1 – сформирована, 2 - выгружена)');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'store_id', 'Идентификатор склада');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'note', 'Примечание к приходной накладной');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'is_duedate', 'Показатель состояни просроченности (не используется)');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'active', 'Показатель состояния активности приходной накладной (0 - не активна, 1 - активна');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'vat_included', 'Дата документа приходной накладной');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'doc_date', 'Дата Обновлено приходной накладной');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'exported_at', 'Дата и время выгрузки приходной накладной');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_waybill` comment "";');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'agent_uuid');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'org');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'order_id');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'num_code');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'text_code');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'readytoexport');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'status_id');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'store_id');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'note');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'is_duedate');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'active');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'vat_included');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'doc_date');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'exported_at');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'updated_at');
    }
}
