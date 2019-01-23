<?php

use api_web\components\Registry;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;
use yii\db\Migration;

/**
 * Class m190115_133042_delete_extra_params_from_settings
 */
class m190115_133042_delete_extra_params_from_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $ar = ['auth_login', 'auth_password', 'application_id', 'application_secret'];
        foreach ($ar as $settingName) {
            $model = IntegrationSetting::findOne(['name' => $settingName, 'service_id' => Registry::POSTER_SERVICE_ID]);
            if ($model) {
                IntegrationSettingValue::deleteAll(['setting_id' => $model->id]);
                if (!$model->delete()) {
                    throw new \Exception($model->getFirstErrors());
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190115_133042_delete_extra_params_from_settings cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190115_133042_delete_extra_params_from_settings cannot be reverted.\n";

        return false;
    }
    */
}
