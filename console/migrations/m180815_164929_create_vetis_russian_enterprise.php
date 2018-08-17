<?php

use yii\db\Migration;

/**
 * Class m180815_164929_create_vetis_russian_enterprise
 */
class m180815_164929_create_vetis_russian_enterprise extends Migration
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
        $this->createTable('{{%vetis_russian_enterprise}}', [
            'uuid' => $this->string()->notNull(),
            'guid' => $this->string()->notNull(),
            'last' => $this->boolean()->null(),
            'active' => $this->boolean()->null(),
            'type' => $this->integer()->null(),
            'next' => $this->string()->null(),
            'previous' => $this->string()->null(),
            'name' => $this->string()->null(),
            'inn' => $this->string()->null(),
            'kpp' => $this->string()->null(),
            'addressView' => $this->string()->null(),
            'data' => $this->text()->null(),
        ]);
        $this->createIndex('vetis_russian_enterprise_uuid', '{{%vetis_russian_enterprise}}', 'uuid');
        $this->createIndex('vetis_russian_enterprise_guid', '{{%vetis_russian_enterprise}}', 'guid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('vetis_russian_enterprise_uuid', '{{%vetis_russian_enterprise}}');
        $this->dropIndex('vetis_russian_enterprise_guid', '{{%vetis_russian_enterprise}}');
        $this->dropTable('{{%vetis_russian_enterprise}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_164929_create_vetis_russian_enterprise cannot be reverted.\n";

        return false;
    }
    */
}
