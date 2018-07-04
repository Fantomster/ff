<?php

use yii\db\Migration;

/**
 * Class m180703_160439_modify_merc_vsd_table
 */
class m180703_160439_modify_merc_vsd_table extends Migration
{
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
        $this->execute("ALTER TABLE `merc_vsd` 
                            ADD COLUMN `raw_data` MEDIUMTEXT NOT NULL AFTER `low_grade_cargo`;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180703_160439_modify_merc_vsd_table cannot be reverted.\n";

        return false;
    }
}
