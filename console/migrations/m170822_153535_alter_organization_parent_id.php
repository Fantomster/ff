<?php

use yii\db\Migration;

class m170822_153535_alter_organization_parent_id extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'parent_id', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'parent_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170822_153535_alter_organization_parent_id cannot be reverted.\n";

        return false;
    }
    */
}
