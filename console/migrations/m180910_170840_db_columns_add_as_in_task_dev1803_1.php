<?php

use yii\db\Migration;

/**
 * Class m180910_170840_db_columns_add_as_in_task_dev1803_1
 */
class m180910_170840_db_columns_add_as_in_task_dev1803_1 extends Migration
{

    public function safeUp()
    {
        #table `order`
        $this->addColumn('{{%order}}', 'service_id', $this->integer(11));
        $this->addColumn('{{%order}}', 'status_updated_at', $this->timestamp());
        $this->execute('ALTER TABLE `order` ADD COLUMN `edi_order` VARCHAR(45) NULL;');
        #table `order_content`
        $this->execute('ALTER TABLE `order_content` ADD COLUMN `edi_ordersp` VARCHAR(45) NULL;');
        $this->execute('ALTER TABLE `order_content` ADD COLUMN `merc_uuid` VARCHAR(36) NULL;');
        $this->addColumn('{{%order_content}}', 'vat_product', $this->integer(11));
    }

    public function safeDown()
    {
        #table `order_content`
        $this->dropColumn('{{%order_content}}', 'vat_product');
        $this->dropColumn('{{%order_content}}', 'merc_uuid');
        $this->dropColumn('{{%order_content}}', 'edi_ordersp');
        #table `order`
        $this->dropColumn('{{%order}}', 'edi_order');
        $this->dropColumn('{{%order}}', 'status_updated_at');
        $this->dropColumn('{{%order}}', 'service_id');
    }

}
