<?php

use yii\db\Migration;

class m181115_160921_add_comments_fields_assorti9 extends Migration
{
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%order_content}}', 'into_price_vat', 'Цена за единицу товара с НДС из накладной поставщика');
        $this->addCommentOnColumn('{{%organization}}', 'user_agreement','Показатель принятия пользовательского соглашения (0 - не подтверждено, 1 - подтверждено)');
        $this->addCommentOnColumn('{{%organization}}', 'confidencial_policy','Показатель принятия политики конфиденциальности (0 - не подтверждено, 1 - подтверждено)');
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'deleted','Показатель состояния удаления товара (0 - не удалён, 1 - удалён)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%order_content}}', 'into_price_vat');
        $this->dropCommentFromColumn('{{%organization}}', 'user_agreement');
        $this->dropCommentFromColumn('{{%organization}}', 'confidencial_policy');
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'deleted');
    }
}