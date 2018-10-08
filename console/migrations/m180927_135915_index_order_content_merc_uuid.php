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
        $this->execute("ALTER TABLE order_content CHANGE `merc_uuid` `merc_uuid` VARCHAR(36) COLLATE utf8_unicode_ci");
        $this->createIndex('ix_order_content_merc_uuid', '{{%order_content}}', 'merc_uuid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('ix_order_content_merc_uuid', '{{%order_content}}');
    }
}
