<?php

use yii\db\Migration;

class m180613_143302_add_comments_table_integration_invoice_content extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `integration_invoice_content` comment "Таблица сведений о строках товаров из таблицы накладной поставщика";');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'invoice_id', 'Идентификатор накладной поставщика');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'row_number', 'Номер строки в таблице накладной поставщика');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'article', 'Артикул/код товара в таблице накладной поставщика');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'title', 'Наименование товара в накладной поставщика');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'percent_nds', 'Налоговая ставка НДС в накладной поставщика');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'price_nds', 'Цена за единицу товара с НДС в накладной поставщика');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'price_without_nds', 'Цена за единицу товара без НДС в накладной поставщика');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'quantity', 'Количество товара в накладной поставщика');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'ed', 'Единица измерения товара в накладной поставщика');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%integration_invoice_content}}', 'sum_without_nds', 'Сумма товаров без НДС в строке накладной поставщика');
    }

    public function safeDown()
    {
        $this->execute('alter table `integration_invoice_content` comment "";');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'id');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'invoice_id');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'row_number');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'article');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'title');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'percent_nds');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'price_nds');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'price_without_nds');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'quantity');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'ed');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'created_at');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'updated_at');
        $this->dropCommentFromColumn('{{%integration_invoice_content}}', 'sum_without_nds');
    }

}
