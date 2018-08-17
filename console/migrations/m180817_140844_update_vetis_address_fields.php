<?php

use yii\db\Migration;

/**
 * Class m180817_140844_update_vetis_address_fields
 */
class m180817_140844_update_vetis_address_fields extends Migration
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
        $this->alterColumn('{{%vetis_business_entity}}', 'addressView', $this->text()->null());
        $this->alterColumn('{{%vetis_russian_enterprise}}', 'addressView', $this->text()->null());
        $this->alterColumn('{{%vetis_foreign_enterprise}}', 'addressView', $this->text()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%vetis_business_entity}}', 'addressView', $this->string()->null());
        $this->alterColumn('{{%vetis_russian_enterprise}}', 'addressView', $this->string()->null());
        $this->alterColumn('{{%vetis_foreign_enterprise}}', 'addressView', $this->string()->null());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180817_140844_update_vetis_address_fields cannot be reverted.\n";

        return false;
    }
    */
}
