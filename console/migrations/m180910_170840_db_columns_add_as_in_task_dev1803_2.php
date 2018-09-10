<?php

use yii\db\Migration;

/**
 * Class m180910_170840_db_columns_add_as_in_task_dev1803_2
 */
class m180910_170840_db_columns_add_as_in_task_dev1803_2 extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        #table `waybill`
        $this->execute('ALTER TABLE `waybill` ADD COLUMN `edi_number` VARCHAR(45) NULL;');
        $this->execute('ALTER TABLE `waybill` ADD COLUMN `edi_recadv` VARCHAR(45) NULL;');
        $this->execute('ALTER TABLE `waybill` ADD COLUMN `edi_invoice` VARCHAR(45) NULL;');
        $this->addColumn('{{%waybill}}', 'doc_date', $this->timestamp());
        $this->addColumn('{{%waybill}}', 'is_duedate', $this->tinyInteger());
        $this->addColumn('{{%waybill}}', 'is_deleted', $this->tinyInteger());
        $this->addColumn('{{%waybill}}', 'created_at', $this->timestamp());
        $this->addColumn('{{%waybill}}', 'updated_at', $this->timestamp());
        $this->addColumn('{{%waybill}}', 'exported_at', $this->timestamp());
        $this->addColumn('{{%waybill}}', 'paymant_dalay', $this->integer(11));
        $this->addColumn('{{%waybill}}', 'paymant_dalay_date', $this->timestamp());
        #table `waybill_content`
        $this->execute('ALTER TABLE `waybill_content` ADD COLUMN `edi_desadv` VARCHAR(45) NULL;');
        $this->execute('ALTER TABLE `waybill_content` ADD COLUMN `edi_alcdes` VARCHAR(45) NULL;');
        $this->addColumn('{{%waybill_content}}', 'sum_with_vat', $this->integer(11));
        $this->addColumn('{{%waybill_content}}', 'sum_without_vat', $this->integer(11));
        $this->addColumn('{{%waybill_content}}', 'price_with_vat', $this->integer(11));
        $this->addColumn('{{%waybill_content}}', 'price_without_vat', $this->integer(11));
    }

    public function safeDown()
    {
        #table `waybill_content`
        $this->dropColumn('{{%waybill_content}}', 'price_without_vat');
        $this->dropColumn('{{%waybill_content}}', 'price_with_vat');
        $this->dropColumn('{{%waybill_content}}', 'sum_without_vat');
        $this->dropColumn('{{%waybill_content}}', 'sum_with_vat');
        $this->dropColumn('{{%waybill_content}}', 'edi_alcdes');
        $this->dropColumn('{{%waybill_content}}', 'edi_desadv');
        #table `waybill`
        $this->dropColumn('{{%waybill}}', 'paymant_dalay_date');
        $this->dropColumn('{{%waybill}}', 'paymant_dalay');
        $this->dropColumn('{{%waybill}}', 'exported_at');
        $this->dropColumn('{{%waybill}}', 'updated_at');
        $this->dropColumn('{{%waybill}}', 'created_at');
        $this->dropColumn('{{%waybill}}', 'is_deleted');
        $this->dropColumn('{{%waybill}}', 'is_duedate');
        $this->dropColumn('{{%waybill}}', 'doc_date');
        $this->dropColumn('{{%waybill}}', 'edi_invoice');
        $this->dropColumn('{{%waybill}}', 'edi_recadv');
        $this->dropColumn('{{%waybill}}', 'edi_number');
    }



}
