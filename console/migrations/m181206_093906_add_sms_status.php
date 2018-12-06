<?php

use yii\db\Migration;

/**
 * Class m181206_093906_add_sms_status
 */
class m181206_093906_add_sms_status extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%sms_status}}', ['status' => 0, 'text' => 'готовится к отправлению']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m181206_093906_add_sms_status cannot be reverted.\n";

      return false;
      }
     */
}
