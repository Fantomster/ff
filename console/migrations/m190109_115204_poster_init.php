<?php

use common\models\AllService;
use common\models\IntegrationSetting;
use common\models\licenses\License;
use common\models\OuterDictionary;
use yii\db\Migration;

/**
 * Class m190109_115204_poster_init
 */
class m190109_115204_poster_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $posterService = new AllService([
            'type_id'   => 1,
            'is_active' => 1,
            'denom'     => 'Poster',
            'vendor'    => 'Poster']);
        $posterService->save();

        $arLicense = [
            'MC Light'      => 101,
            'MC Businesses' => 102,
            'MC Enterprise' => 103,
            'Poster'        => $posterService->id,
        ];
        foreach ($arLicense as $key => $value) {
            $license = License::findOne(['name' => $key]);
            $license->service_id = $value;
            $license->save();
        }

        $arSettings = [
            ['auth_login', 'login', 'Логин пользователя для подключения', 'input_text'],
            ['auth_password', 'password', 'Пароль для подключения', 'password'],
            ['access_token', '', 'Токен подключения', 'password'],
            ['auto_unload_invoice', '0', 'Автоматическая выгрузка накладных', 'dropdown_list'],
            ['application_id', '0', 'Id приложения в системе Poster, указан в настройках приложения на dev.joinposter.com', 'input_text'],
            ['application_secret', '0', 'Секретный код приложения выданные при регистрации. Указан в настройках приложения на dev.joinposter.com', 'input_text'],
        ];

        foreach ($arSettings as list($name, $defaultValue, $comment, $type)) {
            $model = new IntegrationSetting([
                'name'          => $name,
                'default_value' => $defaultValue,
                'comment'       => $comment,
                'type'          => $type,
                'is_active'     => 1,
            ]);

            if ($name == 'auto_unload_invoice') {
                $model->item_list = json_encode([0 => 'Выключено',
                                                 1 => 'Включено',
                                                 2 => 'Полуавтомат',]);
            }
            $model->service_id = $posterService->id;
            if (!$model->save()) {
                throw new Exception($model->getFirstErrors());
            }
        }

        $arPosterDicts = [
            'agent',
            'product',
            'unit',
            'store',
        ];

        foreach ($arPosterDicts as $name) {
            $model = new OuterDictionary([
                'name'       => $name,
                'service_id' => $posterService->id,
            ]);
            $model->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190109_115204_poster_init cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190109_115204_poster_init cannot be reverted.\n";

        return false;
    }
    */
}
