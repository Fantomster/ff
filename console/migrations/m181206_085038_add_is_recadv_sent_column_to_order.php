<?php

use yii\db\Migration;

/**
 * Class m181206_085038_add_is_recadv_sent_column_to_order
 */
class m181206_085038_add_is_recadv_sent_column_to_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'is_recadv_sent', $this->boolean()->defaultValue(0));
        $this->addCommentOnColumn('{{%order}}', 'is_recadv_sent', 'Отправлен ли recadv');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'is_recadv_sent');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181206_085038_add_is_recadv_sent_column_to_order cannot be reverted.\n";

        return false;
    }
    */
}
