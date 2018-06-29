<?php

use yii\db\Migration;

/**
 * Class m180629_142937_edit_mercVSD
 */
class m180629_142937_edit_mercVSD extends Migration
{
    public $tableName = '{{%merc_log}}';

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
        $this->execute( "ALTER TABLE `merc_vsd` 
        CHANGE COLUMN `production_date` `production_date` VARCHAR(255) NULL DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180528_103750_edit_column_mercLog cannot be reverted.\n";

        return false;
    }
}
