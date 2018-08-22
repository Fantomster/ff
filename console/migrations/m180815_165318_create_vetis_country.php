<?php

use yii\db\Migration;

/**
 * Class m180815_165318_create_vetis_country
 */
class m180815_165318_create_vetis_country extends Migration
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
        $this->createTable('{{%vetis_country}}', [
            'uuid' => $this->string()->notNull(),
            'guid' => $this->string()->notNull(),
            'last' => $this->boolean()->null(),
            'active' => $this->boolean()->null(),
            'status' => $this->integer()->null(),
            'next' => $this->string()->null(),
            'previous' => $this->string()->null(),
            'name' => $this->string()->null(),
            'fullName' => $this->string()->null(),
            'englishName' => $this->string()->null(),
            'code' => $this->string(5)->null(),
            'code3' => $this->string(5)->null(),
            'createDate' => $this->dateTime()->null(),
            'updateDate' => $this->dateTime()->null(),
            'data' => $this->text()->null(),
        ]);
        $this->createIndex('vetis_country_uuid', '{{%vetis_country}}', 'uuid');
        $this->createIndex('vetis_country_guid', '{{%vetis_country}}', 'guid');
        $this->createIndex('vetis_country_code', '{{%vetis_country}}', 'code');
        $this->createIndex('vetis_country_code3', '{{%vetis_country}}', 'code3');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('vetis_country_uuid', '{{%vetis_country}}');
        $this->dropIndex('vetis_country_guid', '{{%vetis_country}}');
        $this->dropIndex('vetis_country_code', '{{%vetis_country}}');
        $this->dropIndex('vetis_country_code3', '{{%vetis_country}}');
        $this->dropTable('{{%vetis_country}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_165318_create_vetis_country cannot be reverted.\n";

        return false;
    }
    */
}
