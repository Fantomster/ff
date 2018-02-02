<?php

use yii\db\Migration;

/**
 * Class m180201_141246_defvalues_for_rk_storetree_prnt
 */
class m180201_141246_defvalues_for_rk_storetree_prnt extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE `api`.`rk_storetree` CHANGE COLUMN `prnt` `prnt` INT(11) NOT NULL DEFAULT 0');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE `api`.`rk_storetree` CHANGE COLUMN `prnt` `prnt` INT(11) NOT NULL');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180201_141246_defvalues_for_rk_storetree_prnt cannot be reverted.\n";

        return false;
    }
    */
}
