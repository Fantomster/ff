<?php

use yii\db\Migration;

/**
 * Class m180212_161156_add_last_activity_to_integration_rk_service
 */
class m180212_161156_add_last_activity_to_integration_rk_service extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%rk_service}}', 'last_active', $this->dateTime());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%rk_service}}', 'last_active');
    }
}
