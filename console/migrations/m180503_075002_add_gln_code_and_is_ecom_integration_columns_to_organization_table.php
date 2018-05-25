<?php

use yii\db\Migration;

/**
 * Handles adding gln_code_and_is_ecom_integration to table `organization`.
 */
class m180503_075002_add_gln_code_and_is_ecom_integration_columns_to_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'gln_code', $this->string(50));
        $this->addColumn('{{%organization}}', 'is_ecom_integration', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'gln_code');
        $this->dropColumn('{{%organization}}', 'is_ecom_integration');
    }
}
