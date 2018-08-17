<?php

use yii\db\Migration;

class m180817_121740_add_comments_table_rk_waybill extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_waybill` comment "Таблица сведений о приходных накладных в системе R-Keeper";');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'order_id','Номер заказа приходной накладной');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'doc_date','Дата Обновлено приходной накладной');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'corr_rid','Код контрагента в R-Keeper');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'store_rid','Идентификатор склада в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'active','Показатель состояния активности приходной накладной (0 - не активна, 1 - активна)');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'note','Примечание к приходной накладной');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'text_code','Номер счёта-фактуры');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'num_code','Номер документа по приходной накладной');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'is_duedate','Показатель состояни просроченности (не используется)');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'status_id','Показатель статуса приходной накладной (0 – не сформирована, 1 – сформирована, 2 - выгружена)');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'exported_at','Дата и время выгрузки приходной накладной');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'readytoexport','Показатель состояния готовности к выгрузке приходной накладной (0 – не готова, 1 - готова)');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'org','Идентификатор организации-ресторана, в который осуществлена поставка');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'vat_included','Показатель состояния учёта включения НДС в цену (0 - не включает, 1 - включает)');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'vat_included','Показатель состояния учёта включения НДС в цену (0 - не включает, 1 - включает)');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'vat_included','Показатель состояния учёта включения НДС в цену (0 - не включает, 1 - включает)');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'org','Идентификатор организации-ресторана, в который осуществлена поставка');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'org','Идентификатор организации-ресторана, в который осуществлена поставка');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_waybill` comment "";');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'id');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'order_id');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'doc_date');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'corr_rid');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'store_rid');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'active');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'note');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'text_code');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'num_code');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'is_duedate');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'status_id');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'exported_at');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'readytoexport');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'org');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'vat_included');
    }
}
