<?php

use yii\db\Migration;

/**
 * Class m180202_165518_change_goodsgroup_const_type
 */
class m180202_165518_change_goodsgroup_const_type extends Migration
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
        $this->execute("UPDATE rk_dicconst set type = 7 where denom = 'defGoodGroup';");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180202_165518_change_goodsgroup_const_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180202_165518_change_goodsgroup_const_type cannot be reverted.\n";

        return false;
    }
    */
}
