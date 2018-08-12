<?php

use yii\db\Migration;

/**
 * Class m180812_175240_change_price_column_type_in_request_callback
 */
class m180812_175240_change_price_column_type_in_request_callback extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%request_callback}}', 'price', $this->string(100));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%request_callback}}', 'price', $this->decimal(10,2)->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180812_175240_change_price_column_type_in_request_callback cannot be reverted.\n";

        return false;
    }
    */
}
