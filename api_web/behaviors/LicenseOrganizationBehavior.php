<?php

namespace api_web\behaviors;

use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;
use common\models\licenses\LicenseOrganization;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class LicenseOrganizationBehavior extends Behavior
{
    /** @var LicenseOrganization $model */
    public $model;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert'
        ];
    }

    public function afterInsert($event)
    {
        $this->createDictionary($event);
        $this->createIntegrationSettings($event);
    }

    /**
     * Создание категорий, для подключаемой интеграции
     *
     * @param $event
     */
    private function createDictionary($event)
    {
        $service_id = $this->model->license->service_id;
        //Если у лицензии есть сервис
        if (!empty($service_id)) {
            //Список справочников необходимых для интеграции
            $dictionaryList = OuterDictionary::findAll(['service_id' => $service_id]);
            if (!empty($dictionaryList)) {
                //Проверяем, нет ли уже справочников для этой организации в этой интеграции
                //Если нет, создаем
                foreach ($dictionaryList as $dictionary) {
                    $exists = OrganizationDictionary::find()->where([
                        'org_id'       => $this->model->org_id,
                        'outer_dic_id' => $dictionary->id
                    ])->exists();

                    if (!$exists) {
                        $model = new OrganizationDictionary([
                            'outer_dic_id' => $dictionary->id,
                            'org_id'       => $this->model->org_id,
                            'status_id'    => OrganizationDictionary::STATUS_DISABLED,
                            'count'        => 0
                        ]);
                        $model->save();
                    }
                }
            }
        }
    }

    /**
     * Создание настроек при выдаче лицензии
     *
     * @param $event
     */
    private function createIntegrationSettings($event)
    {
        $service_id = $this->model->license->service_id;
        //Если у лицензии есть сервис
        if (!empty($service_id)) {
            //Список настроек необходимых для интеграции
            $settingList = IntegrationSetting::findAll(['service_id' => $service_id]);
            if (!empty($settingList)) {
                //Проверяем, нет ли уже настройки для этой организации в этой интеграции
                //Если нет, создаем
                foreach ($settingList as $setting) {
                    $exists = IntegrationSettingValue::find()->where([
                        'org_id'     => $this->model->org_id,
                        'setting_id' => $setting->id
                    ])->exists();

                    if (!$exists) {
                        $model = new IntegrationSettingValue([
                            'setting_id' => $setting->id,
                            'org_id'     => $this->model->org_id,
                            'value'      => $setting->default_value
                        ]);
                        $model->save();
                    }
                }
            }
        }
    }
}