<?php

use yii\db\Migration;

/**
 * Class m180711_080305_alter_order_assignment_table
 */
class m180711_080305_alter_order_assignment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%order_assignment}}', 'order_id', $this->integer()->unique()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%order_assignment}}', 'order_id', $this->integer()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180711_080305_alter_order_assignment_table cannot be reverted.\n";

        return false;
    }
    */
}
