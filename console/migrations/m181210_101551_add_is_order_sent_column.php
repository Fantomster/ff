<?php

use yii\db\Migration;

/**
 * Class m181210_101551_add_is_order_sent_column
 */
class m181210_101551_add_is_order_sent_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'is_edi_sent_order', $this->boolean()->defaultValue(false));
        $this->addCommentOnColumn('{{%order}}', 'is_edi_sent_order', 'Флаг, показывающий, отпрален ли документ ORDER');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'is_edi_sent_order');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181210_101551_add_is_order_sent_column cannot be reverted.\n";

        return false;
    }
    */
}
