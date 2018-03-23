<?php

use yii\db\Migration;

/**
 * Class m180320_123157_add_user_org_field_to_email_and_sms_notification_table
 */
class m180320_123157_add_user_org_field_to_email_and_sms_notification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%email_notification}}', 'rel_user_org_id', $this->integer()->after('user_id'));
        $this->addColumn('{{%sms_notification}}', 'rel_user_org_id', $this->integer()->after('user_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%email_notification}}', 'rel_user_org_id');
        $this->dropColumn('{{%sms_notification}}', 'rel_user_org_id');
    }
}
