<?php

use yii\db\Migration;

/**
 * Class m180516_140240_change_pconst_value_type
 */
class m180516_140240_change_pconst_value_type extends Migration
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
        $this->execute('ALTER TABLE `rk_pconst` CHANGE COLUMN `value` `value` TEXT DEFAULT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE `rk_pconst` CHANGE COLUMN `value` `value` VARCHAR(255) DEFAULT NULL');
    }

}
