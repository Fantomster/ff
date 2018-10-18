<?php

use yii\db\Migration;

/**
 * Class m181018_065256_create_edi_tables
 */
class m181018_065256_create_edi_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `edi_provider` (
                              `id` INT NOT NULL AUTO_INCREMENT,
                              `name` VARCHAR(100) NOT NULL,
                              `legal_name` VARCHAR(255) NULL,
                              `web_site` VARCHAR(45) NULL,
                              PRIMARY KEY (`id`))
                            ENGINE = InnoDB;");


        $this->execute("CREATE TABLE IF NOT EXISTS `organization_gln` (
                              `id` INT NOT NULL AUTO_INCREMENT,
                              `org_id` INT NOT NULL,
                              `gln_number` VARCHAR(45) NULL,
                              `edi_provider_id` INT NULL,
                              `gln_default_flag` TINYINT NULL,
                              PRIMARY KEY (`id`),
                              CONSTRAINT `org_id`
                                FOREIGN KEY (`id`)
                                REFERENCES `organization` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION)
                            ENGINE = InnoDB");

        
        $this->execute("CREATE TABLE IF NOT EXISTS `roaming_map` (
                              `id` INT NOT NULL AUTO_INCREMENT,
                              `acquire_id` INT NOT NULL,
                              `acquire_gln_id` INT NULL,
                              `acquire_provuder_id` INT NOT NULL,
                              `vendor_id` INT NOT NULL,
                              `vendor_gln_id` INT NULL,
                              `vendor_provider_id` INT NOT NULL,
                              PRIMARY KEY (`id`),
                              INDEX `acquire_name_idx` (`acquire_id` ASC),
                              INDEX `vendor_id_idx` (`vendor_id` ASC),
                              INDEX `acquire_gln_id_idx` (`acquire_gln_id` ASC),
                              INDEX `vendor_gln_id_idx` (`vendor_gln_id` ASC),
                              CONSTRAINT `acquire_id_roaming_map`
                                FOREIGN KEY (`acquire_id`)
                                REFERENCES `organization` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION,
                              CONSTRAINT `vendor_id_roaming_map`
                                FOREIGN KEY (`vendor_id`)
                                REFERENCES `organization` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION,
                              CONSTRAINT `provider_id_roaming_map`
                                FOREIGN KEY (`id`)
                                REFERENCES `edi_provider` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION,
                              CONSTRAINT `acquire_gln_id_roaming_map`
                                FOREIGN KEY (`acquire_gln_id`)
                                REFERENCES `organization_gln` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION,
                              CONSTRAINT `vendor_gln_id_roaming_map`
                                FOREIGN KEY (`vendor_gln_id`)
                                REFERENCES `organization_gln` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION)
                            ENGINE = InnoDB");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP TABLE  `roaming_map`");
        $this->execute("DROP TABLE  `edi_provider`");
        $this->execute("DROP TABLE  `organization_gln`");
    }


}
