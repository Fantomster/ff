<?php

use yii\db\Migration;

/**
 * Class m181221_084329_add_setting_sh_version
 */
class m181221_084329_add_setting_sh_version extends Migration
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
        $this->insert(\common\models\IntegrationSetting::tableName(), [
            'name'          => 'sh_version',
            'default_value' => 4,
            'comment'       => 'Версия Store House ресторана',
            'type'          => 'dropdown_list',
            'is_active'     => 1,
            'item_list'     => '{"4":"Store House v.4", "5":"Store House v.5"}',
            'service_id'    => \api_web\components\Registry::RK_SERVICE_ID
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $setting = \common\models\IntegrationSetting::findOne([
            'service_id' => \api_web\components\Registry::RK_SERVICE_ID,
            'name'       => 'sh_version'
        ]);

        if ($setting) {
            $this->delete(\common\models\IntegrationSettingValue::tableName(), ['setting_id' => $setting->id]);
            $this->delete(\common\models\IntegrationSetting::tableName(), ['id' => $setting->id]);
        }
    }
}
