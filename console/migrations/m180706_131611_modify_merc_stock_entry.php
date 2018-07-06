<?php

use yii\db\Migration;

/**
 * Class m180706_131611_modify_merc_stock_entry
 */
class m180706_131611_modify_merc_stock_entry extends Migration
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
        $this->execute("ALTER TABLE `merc_stock_entry` 
                            ADD COLUMN `raw_data` MEDIUMTEXT NOT NULL AFTER `vsd_uuid`;");
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
