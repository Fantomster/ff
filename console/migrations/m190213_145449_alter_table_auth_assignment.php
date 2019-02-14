<?php

use yii\db\Migration;

/**
 * Class m190213_145449_alter_table_auth_assignment
 */
class m190213_145449_alter_table_auth_assignment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%auth_assignment}}', 'user_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%auth_assignment}}', 'user_id', $this->string(64));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190213_145449_alter_table_auth_assignment cannot be reverted.\n";

        return false;
    }
    */
}
