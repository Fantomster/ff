<?php

use yii\db\Migration;

/**
 * Class m181113_120518_add_index_license_organization
 */
class m181113_120518_add_index_license_organization extends Migration
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
        $table = \common\models\licenses\LicenseOrganization::tableName();
        $this->createIndex('idx_org_id_license', $table, 'org_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $table = \common\models\licenses\LicenseOrganization::tableName();
        $this->dropIndex('idx_org_id_license', $table);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181113_120518_add_index_license_organization cannot be reverted.\n";

        return false;
    }
    */
}
