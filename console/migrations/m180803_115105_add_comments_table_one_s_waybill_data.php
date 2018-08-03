<?php

use yii\db\Migration;

class m180803_115105_add_comments_table_one_s_waybill_data extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_waybill_data` comment "Таблица сведений о товарных позициях приходных накладных в системе 1С";');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'waybill_id','Идентификатор приходной накладной в системе 1С');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'product_id','Идентификатор товара');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'product_rid','Идентификатор товара в системе 1С');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'munit','Единица измерения товара');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'org','Идентификатор организации-поставщика товаров');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'vat','Ставка НДС');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'vat_included','Стоимость товара с НДС (не используется)');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'sum','Стоимость товара без НДС');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'quant','Количество товара в единицах без измерения');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'defsum','Стоимость товара без НДС согласно начальным данным');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'defquant','Количество товара в единицах измерения согласно начальным данным');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'koef','Коэффициент пересчёта в приходной накладной в системе 1С');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_waybill_data` comment "";');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'waybill_id');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'product_id');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'product_rid');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'munit');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'org');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'vat');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'vat_included');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'sum');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'quant');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'defsum');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'defquant');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'koef');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'created_at');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'updated_at');
    }
}
