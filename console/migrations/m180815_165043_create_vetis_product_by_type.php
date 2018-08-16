<?php

use yii\db\Migration;

/**
 * Class m180815_165043_create_vetis_product_by_type
 */
class m180815_165043_create_vetis_product_by_type extends Migration
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
        echo "m180815_165043_create_vetis_product_by_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_165043_create_vetis_product_by_type cannot be reverted.\n";

        return false;
    }
    */
}
