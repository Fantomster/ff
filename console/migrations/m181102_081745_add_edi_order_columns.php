<?php

use yii\db\Migration;

/**
 * Class m181102_081745_add_edi_order_columns
 */
class m181102_081745_add_edi_order_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'edi_doc_date', $this->string(20)->null());
        $this->addCommentOnColumn('{{%order}}', 'edi_doc_date', 'Дата накладной заказа по EDI');
        $this->addColumn('{{%organization}}', 'lang', $this->string(5)->null()->defaultValue('ru'));
        $this->addCommentOnColumn('{{%organization}}', 'lang', 'Язык организации');
        $this->addColumn('{{%order_content}}', 'edi_shipment_quantity', $this->integer()->null());
        $this->addCommentOnColumn('{{%order_content}}', 'edi_shipment_quantity', 'Отгруженное количество товара EDI');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'edi_doc_date');
        $this->dropColumn('{{%organization}}', 'lang');
    }
}
