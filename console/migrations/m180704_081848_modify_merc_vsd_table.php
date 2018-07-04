<?php

use yii\db\Migration;

/**
 * Class m180704_081848_modify_merc_vsd_table
 */
class m180704_081848_modify_merc_vsd_table extends Migration
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
        $this->execute(" ALTER TABLE `merc_vsd` 
CHANGE COLUMN `low_grade_cargo` `low_grade_cargo` SMALLINT(1) NULL DEFAULT NULL ;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180704_081848_modify_merc_vsd_table cannot be reverted.\n";

        return false;
    }
}
