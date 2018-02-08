<?php

use yii\db\Migration;

/**
 * Class m180205_123858_add_field_request_uid_to_tasks
 */
class m180205_123858_add_field_request_uid_to_tasks extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%rk_tasks}}', 'req_uid', $this->string(45)->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%rk_tasks}}', 'req_uid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180205_123858_add_field_request_uid_to_tasks cannot be reverted.\n";

        return false;
    }
    */
}
