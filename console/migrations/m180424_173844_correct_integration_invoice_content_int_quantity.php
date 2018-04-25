<?php

use yii\db\Migration;

/**
 * Class m180424_173844_correct_integration_invoice_content_int_quantity
 */
class m180424_173844_correct_integration_invoice_content_int_quantity extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE `integration_invoice_content` CHANGE COLUMN `quantity` `quantity` decimal(12,3) default 0;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE `integration_invoice_content` CHANGE COLUMN `quantity` `quantity` int default NULL ;');
    }

}
