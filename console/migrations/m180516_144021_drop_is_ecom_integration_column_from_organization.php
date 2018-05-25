<?php

use yii\db\Migration;

/**
 * Class m180516_144021_drop_is_ecom_integration_column_from_organization
 */
class m180516_144021_drop_is_ecom_integration_column_from_organization extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%organization}}', 'is_ecom_integration');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%organization}}', 'is_ecom_integration', $this->boolean()->defaultValue(0));
    }
}
