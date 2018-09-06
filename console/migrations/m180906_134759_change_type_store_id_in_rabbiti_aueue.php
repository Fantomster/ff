<?php

use yii\db\Migration;

/**
 * Class m180906_134759_change_type_store_id_in_rabbiti_aueue
 */
class m180906_134759_change_type_store_id_in_rabbiti_aueue extends Migration
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
        $this->execute("ALTER TABLE `rabbit_queues` 
                CHANGE COLUMN `store_id` `store_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'ID склада' ;
                ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE `api`.`rabbit_queues` 
            CHANGE COLUMN `store_id` `store_id` INT(11) NULL DEFAULT NULL COMMENT 'ID склада' ;
            ");

        return false;
    }

}
