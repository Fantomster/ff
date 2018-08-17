<?php

use yii\db\Migration;

class m180817_121504_add_comments_table_rk_waybill_data extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_waybill_data` comment "Таблица сведений о товарных позициях приходных накладных в системе R-Keeper";');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'waybill_id','Идентификатор приходной накладной в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'product_rid','Идентификатор товара в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'quant','Количество товара в единицах измерения');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'sum','Стоимость товара без НДС');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'vat','Ставка НДС');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'munit_rid','Идентификатор единицы измерения товара');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'product_id','Идентификатор товара');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'koef','Коэффициент пересчёта в приходной накладной в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'defsum','Стоимость товара без НДС согласно начальным данным');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'defquant','Количество товара в единицах измерения согласно начальным данным');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'org','Идентификатор организации-поставщика товаров');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'vat_included','Стоимость товара с НДС (не используется)');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'linked_at','Дата и время сопоставления товара в приходной накладной');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_waybill_data` comment "";');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'id');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'waybill_id');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'product_rid');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'quant');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'sum');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'vat');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'munit_rid');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'product_id');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'koef');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'defsum');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'defquant');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'org');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'vat_included');
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'linked_at');
    }
}
