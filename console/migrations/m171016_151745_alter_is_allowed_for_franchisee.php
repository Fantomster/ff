<?php

use yii\db\Migration;

class m171016_151745_alter_is_allowed_for_franchisee extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'is_allowed_for_franchisee', $this->boolean()->notNull()->defaultValue(true));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'is_allowed_for_franchisee');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171016_151745_alter_is_allowed_for_franchisee cannot be reverted.\n";

        return false;
    }
    */
}
