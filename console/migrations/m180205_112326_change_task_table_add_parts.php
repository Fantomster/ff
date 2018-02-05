<?php

use yii\db\Migration;

/**
 * Class m180205_112326_change_task_table_add_parts
 */
class m180205_112326_change_task_table_add_parts extends Migration
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
        $this->addColumn('{{%rk_tasks}}','total_parts', $this->integer());
        $this->addColumn('{{%rk_tasks}}','current_part', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%rk_tasks}}','total_parts');
        $this->dropColumn('{{%rk_tasks}}','current_part');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180205_112326_change_task_table_add_parts cannot be reverted.\n";

        return false;
    }
    */
}
