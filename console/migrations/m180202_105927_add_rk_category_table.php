<?php

use yii\db\Migration;

/**
 * Class m180202_105927_add_rk_category_table
 */
class m180202_105927_add_rk_category_table extends Migration
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
        $this->dropTable('{{%rk_category}}');
        $this->execute('CREATE TABLE rk_category SELECT * FROM rk_storetree;');
        $this->execute('TRUNCATE TABLE rk_category;');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180202_105927_add_rk_category_table cannot be reverted.\n";

        return false;
    }


}
