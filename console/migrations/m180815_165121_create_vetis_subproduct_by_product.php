<?php

use yii\db\Migration;

/**
 * Class m180815_165121_create_vetis_subproduct_by_product
 */
class m180815_165121_create_vetis_subproduct_by_product extends Migration
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
        $this->createTable('{{%vetis_subproduct_by_product}}', [
            'uuid' => $this->string()->notNull(),
            'guid' => $this->string()->notNull(),
            'last' => $this->boolean()->null(),
            'active' => $this->boolean()->null(),
            'status' => $this->integer()->null(),
            'next' => $this->string()->null(),
            'previous' => $this->string()->null(),
            'name' => $this->string()->null(),
            'code' => $this->string()->null(),
            'productGuid' => $this->string()->null(),
            'createDate' => $this->dateTime()->null(),
            'updateDate' => $this->dateTime()->null(),
            'data' => $this->text()->null(),
        ]);
        $this->createIndex('vetis_subproduct_by_product_uuid', '{{%vetis_subproduct_by_product}}', 'uuid');
        $this->createIndex('vetis_subproduct_by_product_guid', '{{%vetis_subproduct_by_product}}', 'guid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('vetis_subproduct_by_product_uuid', '{{%vetis_subproduct_by_product}}');
        $this->dropIndex('vetis_subproduct_by_product_guid', '{{%vetis_subproduct_by_product}}');
        $this->dropTable('{{%vetis_subproduct_by_product}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_165121_create_vetis_subproduct_by_product cannot be reverted.\n";

        return false;
    }
    */
}
