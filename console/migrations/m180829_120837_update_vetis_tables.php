<?php

use yii\db\Migration;

/**
 * Class m180829_120837_update_vetis_tables
 */
class m180829_120837_update_vetis_tables extends Migration
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
        $this->alterColumn('{{%vetis_business_entity}}', 'uuid', $this->string()->unique()->notNull());
        $this->alterColumn('{{%vetis_country}}', 'uuid', $this->string()->unique()->notNull());
        $this->alterColumn('{{%vetis_foreign_enterprise}}', 'uuid', $this->string()->unique()->notNull());
        $this->alterColumn('{{%vetis_product_by_type}}', 'uuid', $this->string()->unique()->notNull());
        $this->alterColumn('{{%vetis_product_item}}', 'uuid', $this->string()->unique()->notNull());
        $this->alterColumn('{{%vetis_purpose}}', 'uuid', $this->string()->unique()->notNull());
        $this->alterColumn('{{%vetis_russian_enterprise}}', 'uuid', $this->string()->unique()->notNull());
        $this->alterColumn('{{%vetis_subproduct_by_product}}', 'uuid', $this->string()->unique()->notNull());
        $this->alterColumn('{{%vetis_unit}}', 'uuid', $this->string()->unique()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%vetis_business_entity}}', 'uuid', $this->string()->notNull());
        $this->alterColumn('{{%vetis_country}}', 'uuid', $this->string()->notNull());
        $this->alterColumn('{{%vetis_foreign_enterprise}}', 'uuid', $this->string()->notNull());
        $this->alterColumn('{{%vetis_product_by_type}}', 'uuid', $this->string()->notNull());
        $this->alterColumn('{{%vetis_product_item}}', 'uuid', $this->string()->notNull());
        $this->alterColumn('{{%vetis_purpose}}', 'uuid', $this->string()->notNull());
        $this->alterColumn('{{%vetis_russian_enterprise}}', 'uuid', $this->string()->notNull());
        $this->alterColumn('{{%vetis_subproduct_by_product}}', 'uuid', $this->string()->notNull());
        $this->alterColumn('{{%vetis_unit}}', 'uuid', $this->string()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180829_120837_update_vetis_tables cannot be reverted.\n";

        return false;
    }
    */
}
