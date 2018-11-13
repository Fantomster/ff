<?php

use yii\db\Migration;

/**
 * Class m181030_093259_add_fields_to_order_content_table
 */
class m181030_093259_add_fields_to_order_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_content}}', 'into_quantity', $this->decimal(10, 3)
            ->comment('Кол-во из накладной поставщика'));
        $this->addColumn('{{%order_content}}', 'into_price', $this->decimal(20, 2)
            ->comment('Цена из накладной поставщика'));
        $this->addColumn('{{%order_content}}', 'into_price_vat', $this->integer()
            ->comment('Цена за еденицу товара с НДС из накладной поставщика'));
        $this->addColumn('{{%order_content}}', 'into_price_sum', $this->decimal(20, 2)
            ->comment('Сумма за количество товара из накладной поставщика'));
        $this->addColumn('{{%order_content}}', 'into_price_sum_vat', $this->decimal(20, 2)
            ->comment('Сумма за количество товара с НДС из накладной поставщика'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181030_093259_add_fields_to_order_content_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181030_093259_add_fields_to_order_content_table cannot be reverted.\n";

        return false;
    }
    */
}
