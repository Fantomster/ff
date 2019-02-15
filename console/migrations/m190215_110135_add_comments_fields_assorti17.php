<?php

use yii\db\Migration;

class m190215_110135_add_comments_fields_assorti17 extends Migration
{
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%catalog_base_goods}}', 'barcode','Штрих-код товара на Market Place');
        $this->addCommentOnColumn('{{%catalog_snapshot_content}}', 'barcode','Штрих-код товара на Market Place');
        $this->addCommentOnColumn('{{%edi_order_content}}', 'barcode','Штрих-код товара');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%catalog_base_goods}}', 'barcode');
        $this->dropCommentFromColumn('{{%catalog_snapshot_content}}', 'barcode');
        $this->dropCommentFromColumn('{{%edi_order_content}}', 'barcode');
    }
}
