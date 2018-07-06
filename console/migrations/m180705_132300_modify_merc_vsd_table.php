<?php

use yii\db\Migration;

/**
 * Class m180705_132300_modify_merc_vsd_table
 */
class m180705_132300_modify_merc_vsd_table extends Migration
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
        $this->execute("TRUNCATE TABLE `merc_visits`;");
        $this->execute("TRUNCATE TABLE `merc_vsd`;");
        $this->execute( " 
                            ALTER TABLE `merc_vsd` 
                            ADD UNIQUE INDEX `uuid_UNIQUE` (`uuid` ASC);
                       ");
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
