<?php

use yii\db\Migration;

class m181226_104606_add_comments_fields_assorti14 extends Migration
{
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%catalog_goods}}', 'service_id', 'Идентификатор сервиса интеграции');
        $this->addCommentOnColumn('{{%order_content}}', 'into_quantity','Кол-во из накладной поставщика или из EDI');
        $this->addCommentOnColumn('{{%order_content}}', 'into_price','Цена из накладной поставщика или из EDI');
        $this->addCommentOnColumn('{{%order_content}}', 'into_price_vat','Цена за единицу товара с НДС из накладной поставщика или из EDI');
        $this->addCommentOnColumn('{{%order_content}}', 'into_price_sum','Сумма за количество товара из накладной поставщика или из EDI');
        $this->addCommentOnColumn('{{%order_content}}', 'into_price_sum_vat','Сумма за количество товара с НДС из накладной поставщика или из EDI');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'service_id');
        $this->dropCommentFromColumn('{{%order_content}}', 'into_quantity');
        $this->dropCommentFromColumn('{{%order_content}}', 'into_price');
        $this->dropCommentFromColumn('{{%order_content}}', 'into_price_vat');
        $this->dropCommentFromColumn('{{%order_content}}', 'into_price_sum');
        $this->dropCommentFromColumn('{{%order_content}}', 'into_price_sum_vat');
    }
}
