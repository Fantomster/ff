<?php

use yii\db\Migration;

/**
 * Handles the creation of table `test_vendors`.
 */
class m180310_084220_create_test_vendors_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('test_vendors', [
            'id' => $this->primaryKey(),
            'vendor_id' => $this->integer()->notNull(),
            'guide_name' => $this->string(255),
            'is_active' => $this->boolean()->defaultValue(1),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('test_vendors');
    }
}
