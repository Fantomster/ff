<?php

use yii\db\Migration;

/**
 * Class m180424_111909_add_language_to_user_table
 */
class m180424_111909_add_language_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'language', $this->string()->defaultValue('ru'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'language');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180424_111909_add_language_to_user_table cannot be reverted.\n";

        return false;
    }
    */
}
