<?php

use yii\db\Migration;

/**
 * Class m181008_060646_add_role_order_initiator
 */
class m181008_060646_add_role_order_initiator extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%role}}', ['name' => 'Иницииатор закупки', 'organization_type' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //echo "m181008_060646_add_role_order_initiator cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181008_060646_add_role_order_initiator cannot be reverted.\n";

        return false;
    }
    */
}
