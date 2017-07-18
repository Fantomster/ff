<?php

use yii\db\Migration;

class m170703_075221_change_discount extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%order}}', 'discount', $this->decimal(10,2)->null());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%order}}', 'discount', $this->decimal(2,0)->null());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170703_075221_change_discount cannot be reverted.\n";

        return false;
    }
    */
}
