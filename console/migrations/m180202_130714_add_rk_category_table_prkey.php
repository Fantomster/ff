<?php

use yii\db\Migration;

/**
 * Class m180202_130714_add_rk_category_table_prkey
 */
class m180202_130714_add_rk_category_table_prkey extends Migration
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

        $this->execute('ALTER TABLE `rk_category` CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT;');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180202_130714_add_rk_category_table_prkey cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180202_130714_add_rk_category_table_prkey cannot be reverted.\n";

        return false;
    }
    */
}
