<?php

use yii\db\Migration;

/**
 * Class m180815_164946_create_vetis_foreign_enterprise
 */
class m180815_164946_create_vetis_foreign_enterprise extends Migration
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
        $this->createTable('{{%vetis_foreign_enterprise}}', [
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
            'country_guid' => $this->string()->null(),
            'addressView' => $this->string()->null(),
            'data' => $this->text()->null(),
        ]);
        $this->createIndex('vetis_foreign_enterprise_uuid', '{{%vetis_foreign_enterprise}}', 'uuid');
        $this->createIndex('vetis_foreign_enterprise_guid', '{{%vetis_foreign_enterprise}}', 'guid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('vetis_foreign_enterprise_uuid', '{{%vetis_foreign_enterprise}}');
        $this->dropIndex('vetis_foreign_enterprise_guid', '{{%vetis_foreign_enterprise}}');
        $this->dropTable('{{%vetis_foreign_enterprise}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_164946_create_vetis_foreign_enterprise cannot be reverted.\n";

        return false;
    }
    */
}
