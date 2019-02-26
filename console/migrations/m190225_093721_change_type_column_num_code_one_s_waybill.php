<?php

use yii\db\Migration;

/**
 * Class m190225_093721_change_type_column_num_code_one_s_waybill
 */
class m190225_093721_change_type_column_num_code_one_s_waybill extends Migration
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
        $this->alterColumn('{{%one_s_waybill}}', 'num_code', $this->string(128));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%one_s_waybill}}', 'num_code', $this->bigInteger(11));
    }
}
