<?php

use yii\db\Migration;

/**
 * Class m181025_084233_add_leradata_json_column
 */
class m181025_084233_add_leradata_json_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%edi_files_queue}}', 'json_data', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%edi_files_queue}}', 'json_data');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181025_084233_add_leradata_json_column cannot be reverted.\n";

        return false;
    }
    */
}
