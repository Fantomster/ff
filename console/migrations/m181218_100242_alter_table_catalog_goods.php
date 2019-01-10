<?php

use yii\db\Migration;

/**
 * Class m181218_100242_alter_table_catalog_goods
 */
class m181218_100242_alter_table_catalog_goods extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%catalog_goods}}', 'service_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%catalog_goods}}', 'service_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181218_100242_alter_table_catalog_goods cannot be reverted.\n";

        return false;
    }
    */
}
