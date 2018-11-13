<?php

use yii\db\Migration;

/**
 * Class m181019_110648_add_column_integration_service_id_user_table
 */
class m181019_110648_add_column_integration_service_id_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'integration_service_id', $this->tinyInteger()->comment('ИД интеграции по умолчанию'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'integration_service_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181019_110648_add_column_integration_service_id_user_table cannot be reverted.\n";

        return false;
    }
    */
}
