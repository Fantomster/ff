<?php

use yii\db\Migration;

/**
 * Class m180522_141851_enlarge_file_content_for_invoice
 */
class m180522_141851_enlarge_file_content_for_invoice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE `integration_invoice` CHANGE COLUMN `file_content` `file_content` MEDIUMTEXT DEFAULT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE `integration_invoice` CHANGE COLUMN `file_content` `file_content` TEXT DEFAULT NULL');
    }


}
