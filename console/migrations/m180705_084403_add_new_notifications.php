<?php

use yii\db\Migration;

/**
 * Class m180705_084403_add_new_notifications
 */
class m180705_084403_add_new_notifications extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%additional_email}}', 'merc_vsd', $this->tinyInteger(1)->defaultValue(0)->after('request_accept'));
        $this->addColumn('{{%email_notification}}', 'merc_vsd', $this->tinyInteger(1)->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%additional_email}}', 'merc_vsd');
        $this->dropColumn('{{%email_notification}}', 'merc_vsd');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180705_084403_add_new_notifications cannot be reverted.\n";

        return false;
    }
    */
}
