<?php

use yii\db\Migration;

/**
 * Class m181026_061456_drop_column_merc_uuid_in_waybill_content
 */
class m181026_061456_drop_column_merc_uuid_in_waybill_content extends Migration
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
        $this->dropColumn('waybill_content', 'merc_uuid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('waybill_content', 'merc_uuid', $this->string(255)->null());
    }
}
