<?php

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use common\models\IntegrationSetting;
use yii\db\Migration;

/**
 * Class m190121_092151_add_settings_auth_login_and_auth_password
 */
class m190121_092151_add_settings_auth_login_and_auth_password extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $services = [Registry::MERC_SERVICE_ID, Registry::RK_SERVICE_ID, Registry::TILLYPAD_SERVICE_ID, Registry::IIKO_SERVICE_ID];
        $arSettingNames = [
            'auth_login'    => [
                'default' => 'login',
                'comment' => 'Логин пользователя для подключения',
                'type'    => 'input_text',
            ],
            'auth_password' => [
                'default' => 'password',
                'comment' => 'Пароль для подключения',
                'type'    => 'password',
            ],
        ];
        foreach ($arSettingNames as $settingName => $prop) {
            foreach ($services as $service) {
                if (!IntegrationSetting::find()->where(['service_id' => $service, 'name' => $settingName])->exists())
                    $model = new IntegrationSetting([
                        'name'          => $settingName,
                        'default_value' => $prop['default'],
                        'comment'       => $prop['comment'],
                        'type'          => $prop['type'],
                        'is_active'     => '1',
                        'service_id'    => $service,
                    ]);
                if (!$model->save()) {
                    throw new ValidationException($model->getFirstErrors());
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190121_092151_add_settings_auth_login_and_auth_password cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190121_092151_add_settings_auth_login_and_auth_password cannot be reverted.\n";

        return false;
    }
    */
}
