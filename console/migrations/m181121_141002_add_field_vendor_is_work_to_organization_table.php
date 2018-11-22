<?php

use yii\db\Migration;

/**
 * Class m181121_141002_add_field_vendor_is_work_to_organization_table
 */
class m181121_141002_add_field_vendor_is_work_to_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'vendor_is_work', $this->tinyInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'vendor_is_work');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181121_141002_add_field_vendor_is_work_to_organization_table cannot be reverted.\n";

        return false;
    }
    */
}
