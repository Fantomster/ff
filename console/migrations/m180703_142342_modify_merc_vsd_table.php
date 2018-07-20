<?php

use yii\db\Migration;

/**
 * Class m180703_142342_modify_merc_vsd_table
 */
class m180703_142342_modify_merc_vsd_table extends Migration
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
        $this->execute("TRUNCATE TABLE `merc_vsd`;");
        $this->execute( " 
                            ALTER TABLE `merc_vsd` 
                            DROP COLUMN `guid`,
                            CHANGE COLUMN `type` `type` VARCHAR(255) NULL DEFAULT NULL AFTER `date_doc`,
                            CHANGE COLUMN `recipient_name` `recipient_name` VARCHAR(255) NULL DEFAULT NULL AFTER `status`,
                            ADD COLUMN `form` VARCHAR(45) NULL AFTER `type`,
                            ADD COLUMN `recipient_guid` VARCHAR(255) NULL AFTER `recipient_name`,
                            CHANGE COLUMN `consignor` `sender_guid` VARCHAR(255) NULL DEFAULT NULL AFTER `recipient_guid`,
                            ADD COLUMN `sender_name` VARCHAR(255) NULL AFTER `sender_guid`,
                            ADD COLUMN `finalized` SMALLINT(1) NULL AFTER `sender_name`,
                            ADD COLUMN `last_update_date` DATETIME NULL AFTER `finalized`,
                            ADD COLUMN `vehicle_number` VARCHAR(45) NULL AFTER `last_update_date`,
                            ADD COLUMN `trailer_number` VARCHAR(45) NULL AFTER `vehicle_number`,
                            ADD COLUMN `container_number` VARCHAR(45) NULL AFTER `trailer_number`,
                            ADD COLUMN `transport_storage_type` VARCHAR(45) NULL AFTER `container_number`,
                            ADD COLUMN `product_type` SMALLINT(1) NULL AFTER `transport_storage_type`,
                            ADD COLUMN `gtin` VARCHAR(45) NULL AFTER `unit`,
                            ADD COLUMN `article` VARCHAR(45) NULL AFTER `gtin`,
                            ADD COLUMN `expiry_date` VARCHAR(255) NULL AFTER `production_date`,
                            ADD COLUMN `batch_id` VARCHAR(45) NULL AFTER `expiry_date`,
                            ADD COLUMN `perishable` SMALLINT(1) NULL AFTER `batch_id`,
                            ADD COLUMN `producer_name` VARCHAR(255) NULL AFTER `perishable`,
                            ADD COLUMN `producer_guid` VARCHAR(255) NULL AFTER `producer_name`,
                            ADD COLUMN `low_grade_cargo` VARCHAR(255) NULL AFTER `producer_guid`;");

        $this->execute("TRUNCATE TABLE `merc_visits`;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180703_142342_modify_merc_vsd_table cannot be reverted.\n";

        return false;
    }
}
