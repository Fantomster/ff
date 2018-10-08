<?php

use yii\db\Migration;

/**
 * Handles the creation of table `ecom_integration_config`.
 */
class m181003_135705_create_ecom_integration_config_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('ecom_integration_config', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer(11)->notNull(),
            'provider' => $this->string(255)->notNull()->defaultValue('Provider'),
            'realization' => $this->string(255)->notNull()->defaultValue('Realization'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('ecom_integration_config');
    }
}
