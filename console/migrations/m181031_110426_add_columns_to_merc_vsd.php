<?php

use yii\db\Migration;

/**
 * Class m181031_110426_add_columns_to_merc_vsd
 */
class m181031_110426_add_columns_to_merc_vsd extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }

    /***
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%merc_vsd}}', 'created_at',$this->timestamp()->null()
            ->comment('Дата создания'));
        $this->addColumn('{{%merc_vsd}}', 'updated_at', $this->timestamp()->null()->defaultValue(null)
            ->comment('Дата обновления'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181030_093259_add_fields_to_order_content_table cannot be reverted.\n";

        return false;
    }

}
