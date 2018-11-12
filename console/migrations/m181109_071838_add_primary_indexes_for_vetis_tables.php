<?php

use yii\db\Migration;

/**
 * Class m181109_071838_add_primary_indexes_for_vetis_tables
 */
class m181109_071838_add_primary_indexes_for_vetis_tables extends Migration
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
        $this->alterColumn('{{%vetis_country}}', 'uuid', $this->string(36));
        $this->addPrimaryKey('', '{{%vetis_country}}', 'uuid');
        $this->alterColumn('{{%vetis_product_by_type}}', 'uuid', $this->string(36));
        $this->addPrimaryKey('', '{{%vetis_product_by_type}}', 'uuid');
        $this->alterColumn('{{%vetis_purpose}}', 'uuid', $this->string(36));
        $this->addPrimaryKey('', '{{%vetis_purpose}}', 'uuid');
        $this->alterColumn('{{%vetis_subproduct_by_product}}', 'uuid', $this->string(36));
        $this->addPrimaryKey('', '{{%vetis_subproduct_by_product}}', 'uuid');
        $this->alterColumn('{{%vetis_unit}}', 'uuid', $this->string(36));
        $this->addPrimaryKey('', '{{%vetis_unit}}', 'uuid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%vetis_country}}', 'uuid', $this->string(255));
        $this->dropPrimaryKey('', '{{%vetis_country}}');
        $this->alterColumn('{{%vetis_product_by_type}}', 'uuid', $this->string(255));
        $this->dropPrimaryKey('', '{{%vetis_product_by_type}}');
        $this->alterColumn('{{%vetis_purpose}}', 'uuid', $this->string(255));
        $this->dropPrimaryKey('', '{{%vetis_purpose}}');
        $this->alterColumn('{{%vetis_subproduct_by_product}}', 'uuid', $this->string(255));
        $this->dropPrimaryKey('', '{{%vetis_subproduct_by_product}}');
        $this->alterColumn('{{%vetis_unit}}', 'uuid', $this->string(255));
        $this->dropPrimaryKey('', '{{%vetis_unit}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181109_071838_add_primary_indexes_for_vetis_tables cannot be reverted.\n";

        return false;
    }
    */
}
