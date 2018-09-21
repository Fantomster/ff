<?php

use yii\db\Migration;

/**
 * Class m180918_101609_add_columns_in_vetis_product_item_table
 */
class m180918_101609_add_columns_in_vetis_product_item_table extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('{{%vetis_product_item}}');

        $this->alterColumn('{{%vetis_product_item}}', 'uuid', $this->string(36));
        $this->addPrimaryKey('pk_uuid', '{{%vetis_product_item}}', 'uuid');

        $this->addColumn('{{%vetis_product_item}}', 'packagingType_guid', $this->string(255));
        $this->addColumn('{{%vetis_product_item}}', 'packagingType_uuid', $this->string(255));
        $this->addColumn('{{%vetis_product_item}}', 'unit_uuid', $this->string(255));
        $this->addColumn('{{%vetis_product_item}}', 'unit_guid', $this->string(255));
        $this->addColumn('{{%vetis_product_item}}', 'packagingQuantity', $this->integer());
        $this->addColumn('{{%vetis_product_item}}', 'packagingVolume', $this->decimal(10,6));

        $this->addCommentOnColumn('{{%vetis_product_item}}', 'packagingType_guid', 'Глобальный идентификатор упаковки');
        $this->addCommentOnColumn('{{%vetis_product_item}}', 'packagingType_uuid', 'Идентификатор версии типа упаковки');
        $this->addCommentOnColumn('{{%vetis_product_item}}', 'unit_uuid', 'Идентификатор версии еиницы измерения');
        $this->addCommentOnColumn('{{%vetis_product_item}}', 'unit_guid', 'Глобальный идентификатор еиницы измерения');
        $this->addCommentOnColumn('{{%vetis_product_item}}', 'packagingQuantity', 'Количество единиц упаковки');
        $this->addCommentOnColumn('{{%vetis_product_item}}', 'packagingVolume', 'Объём единицы упаковки товара');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropPrimaryKey('pk_uuid', '{{%vetis_product_item}}');

        $this->dropColumn('{{%vetis_product_item}}', 'packagingType_guid');
        $this->dropColumn('{{%vetis_product_item}}', 'packagingType_uuid');
        $this->dropColumn('{{%vetis_product_item}}', 'unit_uuid');
        $this->dropColumn('{{%vetis_product_item}}', 'unit_guid');
        $this->dropColumn('{{%vetis_product_item}}', 'packagingQuantity');
        $this->dropColumn('{{%vetis_product_item}}', 'packagingVolume');
    }

}
