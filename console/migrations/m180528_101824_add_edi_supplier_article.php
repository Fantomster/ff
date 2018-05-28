<?php

use yii\db\Migration;

/**
 * Class m180528_101824_add_edi_supplier_article
 */
class m180528_101824_add_edi_supplier_article extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('catalog_base_goods', 'edi_supplier_article', $this->string(30));
        $this->addColumn('order_content', 'edi_supplier_article', $this->string(30));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('catalog_base_goods', 'edi_supplier_article');
        $this->dropColumn('order_content', 'edi_supplier_article');
    }
}
