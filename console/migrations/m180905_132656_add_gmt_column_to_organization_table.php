<?php

use yii\db\Migration;

/**
 * Handles adding gmt to table `organization`.
 */
class m180905_132656_add_gmt_column_to_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("{{%organization}}", 'gmt', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("{{%organization}}", 'gmt');
    }
}
