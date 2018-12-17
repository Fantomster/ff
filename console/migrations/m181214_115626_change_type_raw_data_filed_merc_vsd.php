<?php

use yii\db\Migration;

/**
 * Class m181214_115626_change_type_raw_data_filed_merc_vsd
 */
class m181214_115626_change_type_raw_data_filed_merc_vsd extends Migration
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
        $this->alterColumn('{{%merc_vsd}}', 'raw_data', 'LONGTEXT');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%merc_vsd}}','raw_data', 'MEDIUMTEXT');
    }
}
