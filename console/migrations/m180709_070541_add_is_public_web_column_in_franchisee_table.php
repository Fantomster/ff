<?php

use yii\db\Migration;

/**
 * Class m180709_070541_add_is_public_web_column_in_franchisee_table
 */
class m180709_070541_add_is_public_web_column_in_franchisee_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('franchisee', 'is_public_web', $this->boolean()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('franchisee', 'is_public_web');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180709_070541_add_is_public_web_column_in_franchisee_table cannot be reverted.\n";

        return false;
    }
    */
}
