<?php

use yii\db\Migration;

/**
 * Class m180410_131619_delete_order_sequence_table
 */
class m180410_131619_delete_order_sequence_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('relation_order_order_sequence', 'order_sequence');
        $this->dropTable('order_sequence');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180410_131619_delete_order_sequence_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180410_131619_delete_order_sequence_table cannot be reverted.\n";

        return false;
    }
    */
}
