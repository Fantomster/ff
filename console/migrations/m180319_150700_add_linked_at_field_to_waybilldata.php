<?php

use yii\db\Migration;

/**
 * Class m180319_150700_add_linked_at_field_to_waybilldata
 */
class m180319_150700_add_linked_at_field_to_waybilldata extends Migration
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
        $this->addColumn('{{%rk_waybill_data}}', 'linked_at', $this->dateTime());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%rk_waybill_data}}', 'linked_at');
    }

}
