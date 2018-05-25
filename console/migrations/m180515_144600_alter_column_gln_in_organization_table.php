<?php

use yii\db\Migration;

/**
 * Class m180515_144600_alter_column_gln_in_organization_table
 */
class m180515_144600_alter_column_gln_in_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('organization', 'gln_code', $this->bigInteger(17));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('organization', 'gln_code', $this->string(50));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180515_144600_alter_column_gln_in_organization_table cannot be reverted.\n";

        return false;
    }
    */
}
