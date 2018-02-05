<?php

use yii\db\Migration;

/**
 * Class m180205_134052_add_default_value_to_ruuid_rkws
 */
class m180205_134052_add_default_value_to_ruuid_rkws extends Migration
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
        $this->execute('ALTER TABLE `rk_tasks` CHANGE COLUMN `req_uid` `req_uid` VARCHAR(45) NOT NULL DEFAULT 0');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE `rk_tasks` CHANGE COLUMN `req_uid` `req_uid` VARCHAR(45) NOT NULL');
    }
}
