<?php

use yii\db\Migration;

/**
 * Class m180815_165254_create_vetis_unit
 */
class m180815_165254_create_vetis_unit extends Migration
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
        $this->createTable('{{%vetis_unit}}', [
            'uuid' => $this->string()->notNull(),
            'guid' => $this->string()->notNull(),
            'last' => $this->boolean()->null(),
            'active' => $this->boolean()->null(),
            'status' => $this->integer()->null(),
            'next' => $this->string()->null(),
            'previous' => $this->string()->null(),
            'name' => $this->string()->null(),
            'fullName' => $this->string()->null(),
            'commonUnitGuid' => $this->string()->null(),
            'factor' => $this->integer()->null(),
            'createDate' => $this->dateTime()->null(),
            'updateDate' => $this->dateTime()->null(),
            'data' => $this->text()->null(),
        ]);
        $this->createIndex('vetis_unit_uuid', '{{%vetis_unit}}', 'uuid');
        $this->createIndex('vetis_unit_guid', '{{%vetis_unit}}', 'guid');
        $this->createIndex('vetis_unit_commonUnitGuid', '{{%vetis_unit}}', 'commonUnitGuid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('vetis_unit_uuid', '{{%vetis_unit}}');
        $this->dropIndex('vetis_unit_guid', '{{%vetis_unit}}');
        $this->dropIndex('vetis_unit_commonUnitGuid', '{{%vetis_unit}}');
        $this->dropTable('{{%vetis_unit}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_165254_create_vetis_unit cannot be reverted.\n";

        return false;
    }
    */
}
