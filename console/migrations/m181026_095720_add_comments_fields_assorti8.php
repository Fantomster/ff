<?php

use yii\db\Migration;

class m181026_095720_add_comments_fields_assorti8 extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%waybill_content}}', 'sum_with_vat', 'Стоимость товара с НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'sum_without_vat', 'Стоимость товара без НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'price_with_vat', 'Цена товара с НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'price_without_vat', 'Цена товара без НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%waybill_content}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'is_duedate', 'Показатель состояния просроченности (не используется)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%waybill_content}}', 'sum_with_vat');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'sum_without_vat');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'price_with_vat');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'price_without_vat');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'created_at');
        $this->dropCommentFromColumn('{{%waybill_content}}', 'updated_at');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'is_duedate');
    }
}
