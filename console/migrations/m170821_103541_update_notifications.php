<?php

use yii\db\Migration;

class m170821_103541_update_notifications extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%email_notification}}', 'order_created', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%email_notification}}', 'order_canceled', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%email_notification}}', 'order_changed', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%email_notification}}', 'order_processing', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%email_notification}}', 'order_done', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%sms_notification}}', 'order_created', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%sms_notification}}', 'order_canceled', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%sms_notification}}', 'order_changed', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%sms_notification}}', 'order_processing', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%sms_notification}}', 'order_done', $this->boolean()->notNull()->defaultValue(true));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%email_notification}}', 'order_created');
        $this->dropColumn('{{%email_notification}}', 'order_canceled');
        $this->dropColumn('{{%email_notification}}', 'order_changed');
        $this->dropColumn('{{%email_notification}}', 'order_processing');
        $this->dropColumn('{{%email_notification}}', 'order_done');
        $this->dropColumn('{{%sms_notification}}', 'order_created');
        $this->dropColumn('{{%sms_notification}}', 'order_canceled');
        $this->dropColumn('{{%sms_notification}}', 'order_changed');
        $this->dropColumn('{{%sms_notification}}', 'order_processing');
        $this->dropColumn('{{%sms_notification}}', 'order_done');
    }
}
