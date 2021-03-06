<?php

use yii\db\Migration;

/**
 * Class m180601_131723_add_mercury_field_in_merc_vsd
 */
class m180601_131723_add_mercury_field_in_merc_vsd extends Migration
{
    public $tableName = '{{%merc_vsd}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'type', $this->string(255)->null()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180601_110717_add_mercury_field_in_merc_visits cannot be reverted.\n";

        return false;
    }
}
