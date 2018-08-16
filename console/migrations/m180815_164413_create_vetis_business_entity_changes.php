<?php

use yii\db\Migration;

/**
 * Class m180815_164413_create_vetis_business_entity_changes
 */
class m180815_164413_create_vetis_business_entity_changes extends Migration
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
        $this->createTable('vetis_business_entity_changes', [
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
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('vetis_business_entity_changes');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_164413_create_vetis_business_entity_changes cannot be reverted.\n";

        return false;
    }
    */
}
