<?php

use yii\db\Migration;

/**
 * Class m180815_165003_create_vetis_foreign_enterprise_changes
 */
class m180815_165003_create_vetis_foreign_enterprise_changes extends Migration
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
        echo "m180815_165003_create_vetis_foreign_enterprise_changes cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_165003_create_vetis_foreign_enterprise_changes cannot be reverted.\n";

        return false;
    }
    */
}
