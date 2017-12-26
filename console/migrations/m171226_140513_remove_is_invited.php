<?php

use yii\db\Migration;

/**
 * Class m171226_140513_remove_is_invited
 */
class m171226_140513_remove_is_invited extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%organization}}', 'is_invited');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171226_140513_remove_is_invited cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171226_140513_remove_is_invited cannot be reverted.\n";

        return false;
    }
    */
}
