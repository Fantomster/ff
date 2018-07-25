<?php

use yii\db\Migration;

/**
 * Class m180723_121354_add_main_index_field_to_catalog_table
 */
class m180723_115354_add_main_index_field_to_catalog_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%catalog}}', 'main_index', $this->string()->defaultValue('article'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%catalog}}', 'main_index');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180723_121354_add_main_index_field_to_catalog_table cannot be reverted.\n";

        return false;
    }
    */
}
