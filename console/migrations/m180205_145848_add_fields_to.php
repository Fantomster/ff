<?php

use yii\db\Migration;

/**
 * Class m180205_145848_add_fields_to
 */
class m180205_145848_add_fields_to extends Migration
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
        $this->addColumn('{{%rk_service_data}}', 'status_id', $this->integer()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%rk_service_data}}', 'status_id');
    }
}
