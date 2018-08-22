<?php

use yii\db\Migration;

/**
 * Class m180815_165337_create_vetis_purpose
 */
class m180815_165337_create_vetis_purpose extends Migration
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
        $this->createTable('{{%vetis_purpose}}', [
            'uuid' => $this->string()->notNull(),
            'guid' => $this->string()->notNull(),
            'last' => $this->boolean()->null(),
            'active' => $this->boolean()->null(),
            'status' => $this->integer()->null(),
            'next' => $this->string()->null(),
            'previous' => $this->string()->null(),
            'name' => $this->string()->null(),
            'createDate' => $this->dateTime()->null(),
            'updateDate' => $this->dateTime()->null(),
            'data' => $this->text()->null(),
        ]);
        $this->createIndex('vetis_purpose_uuid', '{{%vetis_purpose}}', 'uuid');
        $this->createIndex('vetis_purpose_guid', '{{%vetis_purpose}}', 'guid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('vetis_purpose_uuid', '{{%vetis_purpose}}');
        $this->dropIndex('vetis_purpose_guid', '{{%vetis_purpose}}');
        $this->dropTable('{{%vetis_purpose}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_165337_create_vetis_purpose cannot be reverted.\n";

        return false;
    }
    */
}
