<?php

use yii\db\Migration;

/**
 * Class m180815_165147_create_vetis_product_item
 */
class m180815_165147_create_vetis_product_item extends Migration
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
        $this->createTable('{{%vetis_product_item}}', [
            'uuid' => $this->string()->notNull(),
            'guid' => $this->string()->notNull(),
            'last' => $this->boolean()->null(),
            'active' => $this->boolean()->null(),
            'status' => $this->integer()->null(),
            'next' => $this->string()->null(),
            'previous' => $this->string()->null(),
            'name' => $this->string()->null(),
            'code' => $this->string()->null(),
            'globalID' => $this->string()->null(),
            'productType' => $this->integer()->null(),
            'product_uuid' => $this->string()->null(),
            'product_guid' => $this->string()->null(),
            'subproduct_uuid' => $this->string()->null(),
            'subproduct_guid' => $this->string()->null(),
            'correspondsToGost' => $this->boolean()->null(),
            'gost' => $this->string()->null(),
            'producer_uuid' => $this->string()->null(),
            'producer_guid' => $this->string()->null(),
            'tmOwner_uuid' => $this->string()->null(),
            'tmOwner_guid' => $this->string()->null(),
            'createDate' => $this->dateTime()->null(),
            'updateDate' => $this->dateTime()->null(),
            'data' => $this->text()->null(),
        ]);
        $this->createIndex('vetis_product_item_uuid', '{{%vetis_product_item}}', 'uuid');
        $this->createIndex('vetis_product_item_guid', '{{%vetis_product_item}}', 'guid');
        $this->createIndex('vetis_product_item_globalID', '{{%vetis_product_item}}', 'globalID');
        $this->createIndex('vetis_product_item_product_uuid', '{{%vetis_product_item}}', 'product_uuid');
        $this->createIndex('vetis_product_item_product_guid', '{{%vetis_product_item}}', 'product_guid');
        $this->createIndex('vetis_product_item_subproduct_uuid', '{{%vetis_product_item}}', 'subproduct_uuid');
        $this->createIndex('vetis_product_item_subproduct_guid', '{{%vetis_product_item}}', 'subproduct_guid');
        $this->createIndex('vetis_product_item_producer_uuid', '{{%vetis_product_item}}', 'producer_uuid');
        $this->createIndex('vetis_product_item_producer_guid', '{{%vetis_product_item}}', 'producer_guid');
        $this->createIndex('vetis_product_item_tmOwner_uuid', '{{%vetis_product_item}}', 'tmOwner_uuid');
        $this->createIndex('vetis_product_item_tmOwner_guid', '{{%vetis_product_item}}', 'tmOwner_guid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('vetis_product_item_uuid', '{{%vetis_product_item}}');
        $this->dropIndex('vetis_product_item_guid', '{{%vetis_product_item}}');
        $this->dropIndex('vetis_product_item_globalID', '{{%vetis_product_item}}');
        $this->dropIndex('vetis_product_item_product_uuid', '{{%vetis_product_item}}');
        $this->dropIndex('vetis_product_item_product_guid', '{{%vetis_product_item}}');
        $this->dropIndex('vetis_product_item_subproduct_uuid', '{{%vetis_product_item}}');
        $this->dropIndex('vetis_product_item_subproduct_guid', '{{%vetis_product_item}}');
        $this->dropIndex('vetis_product_item_producer_uuid', '{{%vetis_product_item}}');
        $this->dropIndex('vetis_product_item_producer_guid', '{{%vetis_product_item}}');
        $this->dropIndex('vetis_product_item_tmOwner_uuid', '{{%vetis_product_item}}');
        $this->dropIndex('vetis_product_item_tmOwner_guid', '{{%vetis_product_item}}');
        $this->dropTable('{{%vetis_product_item}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_165147_create_vetis_product_item cannot be reverted.\n";

        return false;
    }
    */
}
