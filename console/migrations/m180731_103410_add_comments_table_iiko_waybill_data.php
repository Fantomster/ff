<?php

use yii\db\Migration;

class m180731_103410_add_comments_table_iiko_waybill_data extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_waybill_data` comment "Таблица сведений о товарных позициях приходных накладных в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'waybill_id','Идентификатор приходной накладной в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'product_id','Идентификатор товара');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'product_rid','Идентификатор товара в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'munit','Единица измерения товара');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'org','Идентификатор организации-поставщика товаров');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'vat','Ставка НДС');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'vat_included','Стоимость товара с НДС (не используется)');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'sum','Стоимость товара без НДС');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'quant','Количество товара в единицах измерения');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'defsum','Стоимость товара без НДС согласно начальным данным');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'defquant','Количество товара в единицах измерения согласно начальным данным');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'koef','Коэффициент пересчёта в приходной накладной в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'linked_at','Дата и время сопоставления товара в приходной накладной');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_waybill_data` comment "";');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'waybill_id');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'product_id');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'product_rid');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'munit');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'org');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'vat');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'vat_included');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'sum');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'quant');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'defsum');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'defquant');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'koef');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'updated_at');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'linked_at');
    }
}
