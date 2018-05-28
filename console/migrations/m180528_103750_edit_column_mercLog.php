<?php

use yii\db\Migration;

/**
 * Class m180528_103750_edit_column_mercLog
 */
class m180528_103750_edit_column_mercLog extends Migration
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
        $this->execute( "ALTER TABLE `merc_log` 
        CHANGE COLUMN `request` `request` MEDIUMTEXT NULL DEFAULT NULL ,
        CHANGE COLUMN `response` `response` MEDIUMTEXT NULL DEFAULT NULL ;");
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
