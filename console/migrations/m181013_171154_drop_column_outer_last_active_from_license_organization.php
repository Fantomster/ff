<?php

use yii\db\Migration;

/**
 * Class m181013_171154_drop_column_outer_last_active_from_license_organization
 */
class m181013_171154_drop_column_outer_last_active_from_license_organization extends Migration
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
        $this->dropColumn('{{%license_organization}}', 'outer_last_active');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181013_171154_drop_column_outer_last_active_from_license_organization cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181013_171154_drop_column_outer_last_active_from_license_organization cannot be reverted.\n";

        return false;
    }
    */
}
