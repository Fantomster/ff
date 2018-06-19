<?php

use yii\db\Migration;

/**
 * Class m180601_110717_add_mercury_field_in_merc_visits
 */
class m180601_110717_add_mercury_field_in_merc_visits extends Migration
{
    public $tableName = '{{%merc_visits}}';

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
            $this->addColumn($this->tableName, 'guid', $this->string(255)->null()->defaultValue(null));
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
