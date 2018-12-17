<?php

use yii\db\Migration;

class m181214_115534_add_comments_fields_assorti13 extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'doc_date', 'Дата и время создания приходной накладной');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'doc_date', 'Дата и время создания приходной накладной');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'doc_date', 'Дата и время создания приходной накладной');
        $this->addCommentOnColumn('{{%waybill_content}}', 'koef', 'Коэффициент пересчёта в приходной накладной в учётной системе');
        $this->addCommentOnColumn('{{%rk_dicconst}}', 'is_active', 'Показатель состояния активности свойства интеграции с R-keeper (0 - не активно, 1 - активно)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'doc_date');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'doc_date');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'doc_date');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'koef');
        $this->dropCommentFromColumn('{{%rk_dicconst}}', 'is_active');
    }
}
