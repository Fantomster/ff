<?php

use yii\db\Migration;

/**
 * Handles the creation of table `edi_order_content`.
 */
class m180530_120329_create_edi_order_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('order_content', 'edi_supplier_article');
        $this->createTable('edi_order_content', [
            'id' => $this->primaryKey(),
            'order_content_id' => $this->integer(),
            'edi_supplier_article' => $this->string(30),
            'doc_type' => $this->smallInteger()->defaultValue(0)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('order_content', 'edi_supplier_article', $this->string(30));
        $this->dropTable('edi_order_content');
    }
}
