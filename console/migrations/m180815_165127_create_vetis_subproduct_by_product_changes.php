<?php

use yii\db\Migration;

/**
 * Class m180815_165127_create_vetis_subproduct_by_product_changes
 */
class m180815_165127_create_vetis_subproduct_by_product_changes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180815_165127_create_vetis_subproduct_by_product_changes cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_165127_create_vetis_subproduct_by_product_changes cannot be reverted.\n";

        return false;
    }
    */
}
