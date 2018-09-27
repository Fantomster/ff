<?php

use yii\db\Migration;

/**
 * Class m180927_135915_index_order_content_merc_uuid
 */
class m180927_135915_index_order_content_merc_uuid extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $db = \common\helpers\DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
        $this->execute("ALTER TABLE {$db}.merc_vsd CHANGE `uuid` `uuid` VARCHAR(36) COLLATE utf8_unicode_ci");
        $this->execute("ALTER TABLE order_content CHANGE `merc_uuid` `merc_uuid` VARCHAR(36) COLLATE utf8_unicode_ci");
        $this->createIndex('ix_merc_vsd_uuid', $db.'.merc_vsd', 'uuid');
        $this->createIndex('ix_merc_vsd_date_doc', $db.'.merc_vsd', 'date_doc');
        $this->createIndex('ix_order_content_merc_uuid', '{{%order_content}}', 'merc_uuid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $db = \common\helpers\DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
        $this->dropIndex('ix_merc_vsd_uuid', $db.'.merc_vsd');
        $this->dropIndex('ix_merc_vsd_date_doc', $db.'.merc_vsd');
        $this->dropIndex('ix_order_content_merc_uuid', '{{%order_content}}');
    }
}
