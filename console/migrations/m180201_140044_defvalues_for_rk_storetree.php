<?php

use yii\db\Migration;

/**
 * Class m180201_140044_defvalues_for_rk_storetree
 */
class m180201_140044_defvalues_for_rk_storetree extends Migration
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
        $this->execute('ALTER TABLE `rk_storetree` CHANGE COLUMN `rid` `rid` INT(11) NOT NULL DEFAULT 0');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE `rk_storetree` CHANGE COLUMN `rid` `rid` INT(11) NOT NULL');
    }

}
