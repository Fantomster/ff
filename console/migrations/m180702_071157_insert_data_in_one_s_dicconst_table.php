<?php

use yii\db\Migration;

/**
 * Class m180702_071157_insert_data_in_one_s_dicconst_table
 */
class m180702_071157_insert_data_in_one_s_dicconst_table extends Migration
{
    public $tableName = '{{%one_s_dicconst}}';
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
        $this->insert($this->tableName, [
            'denom' => 'useAutoNumber',
            'def_value' => '0',
            'comment' => 'Использование автоматической нумерации накладных',
            'type' => 1,
            'is_active' => 1
        ]);

        $this->insert($this->tableName, [
            'denom' => 'useAcceptedDocs',
            'def_value' => '0',
            'comment' => 'Выгружать накладные c последующим проведением',
            'type' => 1,
            'is_active' => 1
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180702_071157_insert_data_in_one_s_dicconst_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180702_071157_insert_data_in_one_s_dicconst_table cannot be reverted.\n";

        return false;
    }
    */
}
