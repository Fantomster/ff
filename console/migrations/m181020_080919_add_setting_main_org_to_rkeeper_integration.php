<?php

use common\models\IntegrationSetting;
use yii\db\Migration;

/**
 * Class m181020_080919_add_setting_main_org_to_rkeeper_integration
 */
class m181020_080919_add_setting_main_org_to_rkeeper_integration extends Migration
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
        $setting = new IntegrationSetting();
        $setting->name = 'main_org';
        $setting->default_value = '';
        $setting->comment = 'Главный бизнес для сопоставления';
        $setting->type = 'input_text';
        $setting->is_active = 1;
        $setting->service_id = 1;
        $setting->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181020_080919_add_setting_main_org_to_rkeeper_integration cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181020_080919_add_setting_main_org_to_rkeeper_integration cannot be reverted.\n";

        return false;
    }
    */
}
