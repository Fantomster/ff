<?php

use yii\db\Migration;

class m180803_114816_add_comments_table_one_s_waybill extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_waybill` comment "Таблица сведений о приходных накладных в системе 1С";');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'agent_uuid','Внутренний идентификатор контрагента в системе 1С');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'org','Идентификатор организации');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'order_id','Идентификаторо заказа, связанного с приходной накладной');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'num_code','Номер документа по приходной накладной');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'readytoexport','Показатель состояния готовности к выгрузке приходной накладной (0 – не готова, 1 - готова)');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'status_id','Показатель статуса приходной накладной (0 – не сформирована, 1 – сформирована, 2 - выгружена)');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'store_id','Идентификатор склада');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'note','Примечание к приходной накладной');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'is_duedate','Показатель состояния просроченности (не используется)');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'active','Показатель состояния активности приходной накладной (0 - не активна, 1 - активна)');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'vat_included','Дата документа приходной накладной');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'doc_date','Дата Обновлено приходной накладной');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'exported_at','Дата и время выгрузки приходной накладной');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'is_invoice','Показатель состояния необходимости проводить документ при загрузке (0 - не надо проводить, 1 - надо проводить)');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'discount','Величина скидки');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'discount_type','Тип скидки (1 - в процентах, 2 - в рублях)');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_waybill` comment "";');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'agent_uuid');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'org');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'order_id');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'num_code');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'readytoexport');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'status_id');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'store_id');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'note');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'is_duedate');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'active');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'vat_included');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'doc_date');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'created_at');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'exported_at');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'updated_at');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'is_invoice');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'discount');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'discount_type');
    }
}
