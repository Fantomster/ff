<?php

use yii\db\Migration;

/**
 * Class m181116_135218_alter_column_outer_product_map
 */
class m181116_135218_alter_column_outer_product_map extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%outer_product_map}}', 'coefficient', $this->decimal(10, 6));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181116_135218_alter_column_outer_product_map cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181116_135218_alter_column_outer_product_map cannot be reverted.\n";

        return false;
    }
    */
}
