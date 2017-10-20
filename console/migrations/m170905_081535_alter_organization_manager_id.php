<?php

use yii\db\Migration;

class m170905_081535_alter_organization_manager_id extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'manager_id', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'manager_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170905_081535_alter_organization_manager_id cannot be reverted.\n";

        return false;
    }
    */
}
