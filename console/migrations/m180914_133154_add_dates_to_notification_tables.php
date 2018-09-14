<?php

use yii\db\Migration;

/**
 * Class m180914_133154_add_dates_to_notification_tables
 */
class m180914_133154_add_dates_to_notification_tables extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%email_notification}}', 'created_at', $this->timestamp()->null());
        $this->addColumn('{{%email_notification}}', 'updated_at', $this->timestamp()->null());
        $this->addColumn('{{%sms_notification}}', 'created_at', $this->timestamp()->null());
        $this->addColumn('{{%sms_notification}}', 'updated_at', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%email_notification}}', 'created_at', $this->timestamp()->null());
        $this->dropColumn('{{%email_notification}}', 'updated_at', $this->timestamp()->null());
        $this->dropColumn('{{%sms_notification}}', 'created_at', $this->timestamp()->null());
        $this->dropColumn('{{%sms_notification}}', 'updated_at', $this->timestamp()->null());
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m180914_133154_add_dates_to_notification_tables cannot be reverted.\n";

      return false;
      }
     */
}
