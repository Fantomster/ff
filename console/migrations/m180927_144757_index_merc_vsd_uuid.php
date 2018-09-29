<?php

use yii\db\Migration;

/**
 * Class m180927_144757_index_merc_vsd_uuid
 */
class m180927_144757_index_merc_vsd_uuid extends Migration
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
        $this->execute("ALTER TABLE merc_vsd CHANGE `uuid` `uuid` VARCHAR(36) COLLATE utf8_unicode_ci");
        $this->createIndex('ix_merc_vsd_uuid', 'merc_vsd', 'uuid');
        $this->createIndex('ix_merc_vsd_date_doc', 'merc_vsd', 'date_doc');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('ix_merc_vsd_uuid', 'merc_vsd');
        $this->dropIndex('ix_merc_vsd_date_doc', 'merc_vsd');
    }
}
