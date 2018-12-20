<?php

use yii\db\Migration;

/**
 * Class m181210_081856_alter_table_egais_request_response
 */
class m181210_081856_alter_table_egais_request_response extends Migration
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
        $this->addColumn('egais_request_response', 'doc_type', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('egais_request_response', 'doc_type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181210_081856_alter_table_egais_request_response cannot be reverted.\n";

        return false;
    }
    */
}
