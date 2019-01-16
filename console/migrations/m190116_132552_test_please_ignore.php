<?php

use yii\db\Migration;

/**
 * Class m190116_132552_test_please_ignore
 */
class m190116_132552_test_please_ignore extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        throw new Exception("ololo i'm ufo driver");
        //oy veyzmir!
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190116_132552_test_please_ignore cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190116_132552_test_please_ignore cannot be reverted.\n";

        return false;
    }
    */
}
