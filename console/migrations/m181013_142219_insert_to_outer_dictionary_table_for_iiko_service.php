\<?php

use yii\db\Migration;

/**
 * Class m181013_142219_insert_to_outer_dictionary_table_for_iiko_service
 */
class m181013_142219_insert_to_outer_dictionary_table_for_iiko_service extends Migration
{
    private $table = 'outer_dictionary';


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
        $this->batchInsert($this->table, ['name', 'service_id'], [
            ['agent', 2],
            ['category', 2],
            ['product', 2],
            ['unit', 2],
            ['store', 2],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181013_142219_insert_to_outer_dictionary_table_for_iiko_service cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181013_142219_insert_to_outer_dictionary_table_for_iiko_service cannot be reverted.\n";

        return false;
    }
    */
}
