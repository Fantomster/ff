<?php

use yii\db\Migration;

/**
 * Class m180730_094156_add_created_at_field_to_email_blacklist_and_fails
 */
class m180730_094156_add_created_at_field_to_email_blacklist_and_fails extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%email_blacklist}}', 'created_at', $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')));
        $this->addColumn('{{%email_fails}}', 'created_at', $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%email_fails}}', 'created_at');
        $this->dropColumn('{{%email_blacklist}}', 'created_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180730_094156_add_created_at_field_to_email_blacklist_and_fails cannot be reverted.\n";

        return false;
    }
    */
}
