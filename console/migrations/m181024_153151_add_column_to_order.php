<?php

use yii\db\Migration;

/**
 * Class m181024_153151_add_column_to_order
 */
class m181024_153151_add_column_to_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'replaced_order_id', $this->integer()->null());
        $this->addCommentOnColumn('{{%order}}', 'replaced_order_id', 'ID заказа который был заменен текущим');
        $this->addForeignKey('replaced_order_id_key', '{{%order}}', 'replaced_order_id', '{{%order}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addForeignKey('replaced_order_id_key', '{{%order}}');
        $this->addColumn('{{%order}}', 'replaced_order_id', $this->integer()->null());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181024_153151_add_column_to_order cannot be reverted.\n";

        return false;
    }
    */
}
