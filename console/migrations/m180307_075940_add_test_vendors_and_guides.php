<?php

use yii\db\Migration;

/**
 * Class m180307_075940_add_test_vendors_and_guides
 */
class m180307_075940_add_test_vendors_and_guides extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $organization = new \common\models\Organization();
        dd($organization);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180307_075940_add_test_vendors_and_guides cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180307_075940_add_test_vendors_and_guides cannot be reverted.\n";

        return false;
    }
    */
}
