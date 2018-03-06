<?php

use yii\db\Migration;

/**
 * Class m180306_123042_truncate_relation_user_organization_table
 */
class m180306_123042_truncate_relation_user_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('relation_user_organization');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180306_123042_truncate_relation_user_organization_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180306_123042_truncate_relation_user_organization_table cannot be reverted.\n";

        return false;
    }
    */
}
