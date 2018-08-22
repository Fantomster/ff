<?php

use yii\db\Migration;

/**
 * Class m180815_162315_create_business_entity
 */
class m180815_162315_create_business_entity extends Migration
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
        $this->createTable('{{%vetis_business_entity}}', [
            'uuid' => $this->string()->notNull(),
            'guid' => $this->string()->notNull(),
            'last' => $this->boolean()->null(),
            'active' => $this->boolean()->null(),
            'type' => $this->integer()->null(),
            'next' => $this->string()->null(),
            'previous' => $this->string()->null(),
            'name' => $this->string()->null(),
            'fullname' => $this->string()->null(),
            'fio' => $this->string()->null(),
            'inn' => $this->string()->null(),
            'kpp' => $this->string()->null(),
            'addressView' => $this->string()->null(),
            'data' => $this->text()->null(),
        ]);
        $this->createIndex('vetis_business_entity_uuid', '{{%vetis_business_entity}}', 'uuid');
        $this->createIndex('vetis_business_entity_guid', '{{%vetis_business_entity}}', 'guid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('vetis_business_entity_uuid', '{{%vetis_business_entity}}');
        $this->dropIndex('vetis_business_entity_guid', '{{%vetis_business_entity}}');
        $this->dropTable('{{%vetis_business_entity}}');
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m180815_162315_create_business_entity cannot be reverted.\n";

      return false;
      }
     */
}
