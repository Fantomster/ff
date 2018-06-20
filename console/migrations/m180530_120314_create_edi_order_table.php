<?php

use yii\db\Migration;

/**
 * Handles the creation of table `edi_order`.
 */
class m180530_120314_create_edi_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('order', 'lang');
        $this->dropColumn('order', 'invoice_number');
        $this->dropColumn('order', 'invoice_date');
        $this->createTable('edi_order', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(),
            'invoice_number' => $this->string(32),
            'invoice_date' => $this->string(20),
            'lang' => $this->string(5)->defaultValue('ru')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('order', 'lang', $this->string(5)->defaultValue('ru'));
        $this->addColumn('order', 'invoice_number', $this->string(32));
        $this->addColumn('order', 'invoice_date', $this->string(20));
        $this->dropTable('edi_order');
    }
}
