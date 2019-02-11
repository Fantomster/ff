<?php

namespace api_web\behaviors;

use api_web\components\Registry;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;
use common\models\licenses\LicenseOrganization;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use common\models\OuterProductType;
use common\models\OuterProductTypeSelected;
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
            $dictionaryList = OuterDictionary::findAll(['service_id' => $service_id, 'is_common' => 0]);
            if (!empty($dictionaryList)) {
                //Проверяем, нет ли уже справочников для этой организации в этой интеграции
                //Если нет, создаем
                /** @var OuterDictionary $dictionary */
                foreach ($dictionaryList as $dictionary) {
                    $this->createServiceDictionary($dictionary, $service_id, $this->model->org_id);
                }
            }
        }
    }

    /**
     * @param OuterDictionary $dictionary
     * @param int             $service_id
     * @param int             $org_id
     */
    private function createServiceDictionary(OuterDictionary $dictionary, int $service_id, int $org_id)
    {
        $exists = OrganizationDictionary::find()->where([
            'org_id'       => $org_id,
            'outer_dic_id' => $dictionary->id
        ])->exists();

        if (!$exists) {
            $status = OrganizationDictionary::STATUS_DISABLED;
            //Для iiko
            if ($service_id == Registry::IIKO_SERVICE_ID && $dictionary->name == 'product_type') {
                $status = OrganizationDictionary::STATUS_ACTIVE;
                $this->addProductTypeSelectedInIiko($org_id);
            }

            $model = new OrganizationDictionary([
                'outer_dic_id' => $dictionary->id,
                'org_id'       => $org_id,
                'status_id'    => $status
            ]);
            $model->count = 0;
            $model->save();
        }
    }

    /**
     * @param $org_id
     */
    private function addProductTypeSelectedInIiko($org_id)
    {
        $goodsType = OuterProductType::find()->where([
            'service_id' => Registry::IIKO_SERVICE_ID,
            'value'      => 'GOODS'
        ])->one();

        $model = new OuterProductTypeSelected();
        $model->org_id = $org_id;
        $model->outer_product_type_id = $goodsType->id;
        $model->selected = 1;
        $model->save();
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
