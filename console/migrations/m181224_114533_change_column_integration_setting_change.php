<?php

use yii\db\Migration;

/**
 * Class m181224_114533_change_column_integration_setting_change
 */
class m181224_114533_change_column_integration_setting_change extends Migration
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
        $this->alterColumn(\common\models\IntegrationSettingChange::tableName(), 'confirmed_user_id', $this->integer(11)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181224_114533_change_column_integration_setting_change cannot be reverted.\n";
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181224_114533_change_column_integration_setting_change cannot be reverted.\n";

        return false;
    }
    */
}
