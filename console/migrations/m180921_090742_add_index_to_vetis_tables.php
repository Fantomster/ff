<?php

use yii\db\Migration;

/**
 * Class m180921_090742_add_index_to_vetis_tables
 */
class m180921_090742_add_index_to_vetis_tables extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('vetis_russian_enterprise_owner_uuid', '{{%vetis_russian_enterprise}}', 'owner_uuid');
        $this->createIndex('vetis_russian_enterprise_owner_guid', '{{%vetis_russian_enterprise}}', 'owner_guid');
        $this->execute("ALTER TABLE vetis_russian_enterprise ADD FULLTEXT INDEX vetis_russian_enterprise_name (name ASC)");

        $this->createIndex('vetis_foreign_enterprise_owner_uuid', '{{%vetis_foreign_enterprise}}', 'owner_uuid');
        $this->createIndex('vetis_foreign_enterprise_owner_guid', '{{%vetis_foreign_enterprise}}', 'owner_guid');
        $this->createIndex('vetis_foreign_enterprise_country_guid', '{{%vetis_foreign_enterprise}}', 'country_guid');
        $this->execute("ALTER TABLE vetis_foreign_enterprise ADD FULLTEXT INDEX vetis_foreign_enterprise_name (name ASC)");

        $this->execute("ALTER TABLE vetis_business_entity ADD FULLTEXT INDEX vetis_business_entity_name (name ASC)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('vetis_russian_enterprise_owner_uuid', '{{%vetis_russian_enterprise}}');
        $this->dropIndex('vetis_russian_enterprise_owner_guid', '{{%vetis_russian_enterprise}}');
        $this->dropIndex('vetis_russian_enterprise_name', '{{%vetis_russian_enterprise}}');

        $this->dropIndex('vetis_foreign_enterprise_owner_uuid', '{{%vetis_foreign_enterprise}}');
        $this->dropIndex('vetis_foreign_enterprise_owner_guid', '{{%vetis_foreign_enterprise}}');
        $this->dropIndex('vetis_foreign_enterprise_country_guid', '{{%vetis_foreign_enterprise}}');
        $this->dropIndex('vetis_foreign_enterprise_name', '{{%vetis_foreign_enterprise}}');

        $this->dropIndex('vetis_business_entity_name', '{{%vetis_business_entity}}');
    }

}
