<?php

use yii\db\Migration;

/**
 * Class m190205_095547_add_fields_to_vetis_product_item_and_create_vetis_ingredients_table
 */
class m190205_095547_add_fields_to_vetis_product_item_and_create_vetis_ingredients_table extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%vetis_product_item}}', 'perishable', $this->tinyInteger(1)->defaultValue(0));
        $this->addColumn('{{%vetis_product_item}}', 'expiration_date', $this->string(255)->null()->defaultValue(null));
        $this->createTable('{{%vetis_ingredients}}', [
            'id'           => $this->primaryKey(11),
            'guid'         => $this->string(255)->comment('GUID продукции из vetis_product_item к которому принадлежит ингредиент'),
            'product_name' => $this->string(255)->comment('Название продукта из таблицы merc_stock_entry.product_name'),
            'amount'       => $this->decimal(10, 3)->notNull()->comment('Кол-во ингредиента необходимое для переработки в одну единицу продукции'),
        ]);

        $this->createIndex('{{%vetis_ingredients_index_guid}}', '{{%vetis_ingredients}}', 'guid');
        $this->addForeignKey('{{%vetis_ingredients_relation_vetis_product_item_guid}}', '{{%vetis_ingredients}}', 'guid', '{{%vetis_product_item}}', 'guid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%vetis_ingredients}}');
        $this->dropColumn('{{%vetis_product_item}}', 'perishable');
        $this->dropColumn('{{%vetis_product_item}}', 'expiration_date');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190205_095547_add_fields_to_vetis_product_item_and_create_vetis_ingredients_table cannot be reverted.\n";

        return false;
    }
    */
}
