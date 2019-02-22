<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_analog}}`.
 */
class m190214_081252_create_product_analog_table extends Migration
{
    public $table = '{{%product_analog}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull()->comment("ID ресторана"),
            'product_id' => $this->integer()->notNull()->comment('ID из таблицы catalog_base_goods'),
            'parent_id' => $this->integer()->comment('id из таблицы product_analog'),
            'sort_value' => $this->integer()->defaultValue(1),
            'coefficient' => $this->decimal(10,2)->comment("Коэффициент")
        ]);

        $this->createIndex('idx_parent', $this->table, 'parent_id');
        $this->createIndex('idx_client', $this->table, 'client_id');
        $this->createIndex('idx_client_product', $this->table, ['client_id', 'product_id']);

        $this->addForeignKey('fk_client', $this->table, 'client_id', \common\models\Organization::tableName(), 'id');
        $this->addForeignKey('fk_product', $this->table, 'product_id', \common\models\CatalogBaseGoods::tableName(), 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
