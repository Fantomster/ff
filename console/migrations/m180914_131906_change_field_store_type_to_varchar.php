<?php

use yii\db\Migration;

/**
 * Class m180914_131906_change_field_store_type_to_varchar
 */
class m180914_131906_change_field_store_type_to_varchar extends Migration
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
        $this->alterColumn('{{%outer_store}}', 'store_type', $this->string(45)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180914_131906_change_field_store_type_to_varchar cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180914_131906_change_field_store_type_to_varchar cannot be reverted.\n";

        return false;
    }
    */
}
