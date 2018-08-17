<?php

use yii\db\Migration;

/**
 * Class m180815_165043_create_vetis_product_by_type
 */
class m180815_165043_create_vetis_product_by_type extends Migration
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
        $this->createTable('{{%vetis_product_by_type}}', [
            'uuid' => $this->string()->notNull(),
            'guid' => $this->string()->notNull(),
            'last' => $this->boolean()->null(),
            'active' => $this->boolean()->null(),
            'status' => $this->integer()->null(),
            'next' => $this->string()->null(),
            'previous' => $this->string()->null(),
            'name' => $this->string()->null(),
            'code' => $this->string()->null(),
            'productType' => $this->integer()->null(),
            'createDate' => $this->dateTime()->null(),
            'updateDate' => $this->dateTime()->null(),
            'data' => $this->text()->null(),
        ]);
        $this->createIndex('vetis_product_by_type_uuid', '{{%vetis_product_by_type}}', 'uuid');
        $this->createIndex('vetis_product_by_type_guid', '{{%vetis_product_by_type}}', 'guid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('vetis_product_by_type_uuid', '{{%vetis_product_by_type}}');
        $this->dropIndex('vetis_product_by_type_guid', '{{%vetis_product_by_type}}');
        $this->dropTable('{{%vetis_product_by_type}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_165043_create_vetis_product_by_type cannot be reverted.\n";

        return false;
    }
    */
}
