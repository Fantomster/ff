<?php

use yii\db\Migration;

/**
 * Class m180706_145812_modify_merc_visit_table
 */
class m180706_145812_modify_merc_visit_table extends Migration
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
        $this->execute("ALTER TABLE `merc_visits` 
                ADD COLUMN `action` VARCHAR(255) NULL AFTER `guid`;
                UPDATE `api`.`merc_visits` SET `action`='loadVsd' WHERE 1;
        ");
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
