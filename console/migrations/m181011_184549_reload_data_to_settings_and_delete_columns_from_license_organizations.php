<?php

use api\common\models\RkService;
use common\models\AllService;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;
use yii\db\Migration;

/**
 * Class m181011_184549_reload_data_to_settings_and_delete_columns_from_license_organizations
 */
class m181011_184549_reload_data_to_settings_and_delete_columns_from_license_organizations extends Migration
{
    /**
     * {@inheritdoc}
     */
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
        $this->batchInsert('{{%integration_setting}}', [
            'name',
            'default_value',
            'comment',
            'type',
            'is_active',
        ], [
            [
                'rkws_code',
                '0',
                'Идентификатор R-keeper',
                'input_text',
                1,
            ],
            [
                'rkws_outer_name',
                '',
                'Имя внешнего объекта - название ресторана внутри UCS, например',
                'input_text',
                1,
            ],
            [
                'rkws_outer_address',
                '',
                'Адрес внешнего объекта - по данным UCS, например',
                'input_text',
                1,
            ],
            [
                'rkws_outer_phone',
                '',
                'Телефон(ы) внешнего объекта',
                'input_text',
                1,
            ],
            [
                'iiko_outer_name',
                '',
                'Имя внешнего объекта - название ресторана внутри UCS, например',
                'input_text',
                1,
            ],
            [
                'iiko_outer_address',
                '',
                'Адрес внешнего объекта - по данным UCS, например',
                'input_text',
                1,
            ],
            [
                'iiko_outer_phone',
                '',
                'Телефон(ы) внешнего объекта',
                'input_text',
                1,
            ],
            [
                'merc_outer_name',
                '',
                'Имя внешнего объекта - название ресторана внутри UCS, например',
                'input_text',
                1,
            ],
            [
                'merc_outer_address',
                '',
                'Адрес внешнего объекта - по данным UCS, например',
                'input_text',
                1,
            ],
            [
                'merc_outer_phone',
                '',
                'Телефон(ы) внешнего объекта',
                'input_text',
                1,
            ],
            [
                'ones_outer_name',
                '',
                'Имя внешнего объекта - название ресторана внутри UCS, например',
                'input_text',
                1,
            ],
            [
                'ones_outer_address',
                '',
                'Адрес внешнего объекта - по данным UCS, например',
                'input_text',
                1,
            ],
            [
                'ones_outer_phone',
                '',
                'Телефон(ы) внешнего объекта',
                'input_text',
                1,
            ],

        ]);

        $rkwsCodes = RkService::find()->select(['org', 'user_id', 'code'])->all();
        $rkwsCodeConst = IntegrationSetting::findOne(['name' => 'rkws_code']);
        /**@var RkService $rkwsCode */
        foreach ($rkwsCodes as $rkwsCode) {
            if ($rkwsCode->code) {
                $ISValue = new IntegrationSettingValue();
                $ISValue->setting_id = $rkwsCodeConst->id;
                $ISValue->org_id = $rkwsCode->org;
                $ISValue->value = $rkwsCode->code;
                $ISValue->save();
            }
        }


        $this->insertData(1, '\api\common\models\RkService', ['rkws_outer_name', 'rkws_outer_address', 'rkws_outer_phone']);
        $this->insertData(2, '\api\common\models\iiko\iikoService', ['iiko_outer_name', 'iiko_outer_address', 'iiko_outer_phone']);
        $this->insertData(4, '\api\common\models\merc\mercService', ['merc_outer_name', 'merc_outer_address', 'merc_outer_phone']);
        $this->insertData(8, '\api\common\models\one_s\OneSService', ['ones_outer_name', 'ones_outer_address', 'ones_outer_phone']);

        $this->dropColumn('license_organization', 'outer_user');
        $this->dropColumn('license_organization', 'outer_name');
        $this->dropColumn('license_organization', 'outer_address');
        $this->dropColumn('license_organization', 'outer_phone');

    }

    private function insertData(int $serviseID, String $serviceClass, array $arSettingsNames): void
    {
        $oldService = $serviceClass::find()->all();
        if ($oldService && is_iterable($oldService)) {
            $service = AllService::findOne(['id' => $serviseID]);
            $arSettings = IntegrationSetting::find()->where(['name' => $arSettingsNames])->indexBy('name')->all();
            foreach ($oldService as $item) {
                foreach ($arSettingsNames as $settingName) {
                    $strServiceSettingName = substr($settingName, 11);
                    $ISValue = new IntegrationSettingValue();
                    $ISValue->setting_id = $arSettings[$settingName]->id;
                    $ISValue->org_id = $item->org;
                    $ISValue->value = $item->{$strServiceSettingName};
                    $ISValue->save();
                }
            }
        }
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181011_184549_reload_data_to_settings_and_delete_columns_from_license_organizations cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181011_184549_reload_data_to_settings_and_delete_columns_from_license_organizations cannot be reverted.\n";

        return false;
    }
    */
}
