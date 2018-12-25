<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use common\helpers\DBNameHelper;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingChange;
use common\models\IntegrationSettingValue;
use common\models\OuterProductMap;
use yii\db\Query;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;

/**
 * Class IntegrationSettingsWebApi
 *
 * @package api_web\classes
 */
class IntegrationSettingsWebApi extends WebApi
{
    /**
     * Список настроек интеграции
     *
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function list(array $post): array
    {
        $this->validateRequest($post, ['service_id']);

        $result = IntegrationSettingValue::find()
            ->select(['int_set.id', 'int_set.name', 'value', 'COALESCE(ch.new_value, NULL) as changed'])
            ->leftJoin(
                IntegrationSetting::tableName() . ' as int_set',
                "int_set.id = " . IntegrationSettingValue::tableName() . ".setting_id"
            )
            ->leftJoin(
                IntegrationSettingChange::tableName() . ' as ch',
                "ch.integration_setting_id = " . IntegrationSettingValue::tableName() . ".setting_id AND " .
                "ch.org_id = " . IntegrationSettingValue::tableName() . ".org_id AND " .
                "ch.is_active = 1"
            )
            ->where(
                IntegrationSettingValue::tableName() . ".org_id = :org and int_set.service_id = :service and int_set.is_active = true", [
                ':org'     => $this->user->organization_id,
                ':service' => $post['service_id']
            ])
            ->orderBy('setting_id')
            ->asArray()->all();

        return $result;
    }

    /**
     * Список настройки интеграции по ее названию
     *
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getSetting(array $post): array
    {
        $this->validateRequest($post, ['service_id', 'name']);

        $result = IntegrationSettingValue::find()
            ->select(['name', 'value'])
            ->leftJoin(
                IntegrationSetting::tableName(),
                IntegrationSetting::tableName() . ".id = " . IntegrationSettingValue::tableName() . ".setting_id"
            )
            ->where(
                "org_id = :org and service_id = :service and is_active = true and name = :name",
                [
                    ':org'     => $this->user->organization_id,
                    ':service' => $post['service_id'],
                    ':name'    => $post['name']
                ]
            )
            ->asArray()
            ->one();

        return $result;
    }

    /**
     * Изменение настроек
     *
     * @param array $post
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function update(array $post): array
    {
        $this->validateRequest($post, ['service_id', 'settings']);
        $result = [];
        foreach ($post['settings'] as $item) {
            try {
                $result[$item['name']] = $this->updateSetting($post['service_id'], $item);
            } catch (\Exception $e) {
                $result[$item['name']] = ['error' => $e->getMessage()];
            }
        }

        return $result;
    }

    /**
     * Изменение настройки
     *
     * @param $service_id
     * @param $request
     * @return mixed|string
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function updateSetting($service_id, $request)
    {
        $this->validateRequest($request, ['name']);

        if (!isset($request['value'])) {
            throw new BadRequestHttpException('empty_param|value');
        }

        $setting = IntegrationSetting::find()
            ->where('name = :name AND service_id = :service_id AND is_active = :is_active', [
                ':name'       => $request['name'],
                ':service_id' => $service_id,
                ':is_active'  => true
            ])
            ->one();

        if (empty($setting)) {
            throw new BadRequestHttpException('integration_setting.not_found');
        }

        $settingValue = IntegrationSettingValue::find()
            ->where([
                'setting_id' => $setting->id,
                'org_id'     => $this->user->organization_id
            ])
            ->one();

        if (empty($setting->required_moderation)) {
            if (empty($settingValue)) {
                $settingValue = new IntegrationSettingValue();
            }
            $settingValue->setAttributes([
                'setting_id' => $setting->id,
                'org_id'     => $this->user->organization_id,
                'value'      => $request['value']
            ]);

            if (!$settingValue->save()) {
                throw new ValidationException($settingValue->getFirstErrors());
            }

            if ($setting->name == 'main_org' && !empty($request['value'])) {
                $this->updateOuterProductMap($this->user->organization_id, $request['value']);
            }

            return $settingValue->value;
        }

        $settingChange = IntegrationSettingChange::find()
            ->where([
                'integration_setting_id' => $setting->id,
                'org_id'                 => $this->user->organization_id,
                'old_value'              => !empty($settingValue) ? $settingValue->value : null,
                'new_value'              => $request['value'],
                'is_active'              => true
            ])
            ->one();

        if (empty($settingChange)) {
            (new IntegrationSettingChange([
                'org_id'                 => $this->user->organization_id,
                'integration_setting_id' => $setting->id,
                'old_value'              => !empty($settingValue) ? $settingValue->value : null,
                'new_value'              => $request['value'],
                'changed_user_id'        => $this->user->id
            ]))->save();
        }

        throw new BadRequestHttpException(\Yii::t('api_web', 'api_web.moderation_setting_save_msg'));
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function rejectChange($request): array
    {
        $this->validateRequest($request, ['service_id', 'setting_id']);

        $setting = IntegrationSetting::find()
            ->where('service_id = :service_id AND id = :id AND is_active = :is_active', [
                ':service_id' => $request['service_id'],
                ':id'         => $request['setting_id'],
                ':is_active'  => true
            ])
            ->one();

        if (empty($setting)) {
            throw new BadRequestHttpException('integration_setting.not_found');
        }

        $settingChange = IntegrationSettingChange::find()
            ->where([
                'integration_setting_id' => $setting->id,
                'org_id'                 => $this->user->organization_id,
                'is_active'              => true
            ])
            ->one();

        if (empty($settingChange)) {
            throw new BadRequestHttpException('integration_setting.not_found');
        }
        $settingChange->is_active = 0;

        return [
            'result' => $settingChange->save()
        ];
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getMainOrganizations($request)
    {
        $this->validateRequest($request, ['service_id']);
        $arSettings = $this->getSettingsToOrgByService($request['service_id']);
        $arRes = [];
        foreach ($arSettings['arOrgs']['result'] as $item) {
            $setting = $arSettings['arSettingToOrg'][$item['id']] ?? null;
            $item['main_org'] = $arRes[$item['id']]['main_org'] ?? false;
            $item['checked'] = false;
            $item['parent_id'] = $setting['parent_id'] != "" ? $setting['parent_id'] : null;
            $arRes[$item['id']] = $item;
            if (!is_null($setting)) {
                if ($setting['parent_id']) {
                    $arRes[$setting['parent_id']]['main_org'] = true;
                    $arRes[$setting['org_id']]['checked'] = true;
                }
            }
        }

        return ['result' => array_values($arRes)];
    }

    /**
     * @param $serviceId
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function getSettingsToOrgByService($serviceId)
    {
        $arOrgs = $this->container->get('UserWebApi')->getUserOrganizationBusinessList();
        $arOrgIds = array_map(function ($el) {
            return (int)$el['id'];
        }, $arOrgs['result']);
        $arSettingToOrg = $this->getSettingsByOrgIds('main_org', $arOrgIds, $serviceId);

        return compact('arOrgs', 'arSettingToOrg', 'arOrgIds');
    }

    /**
     * Получение настройки для всех организаций
     *
     * @param string $settingName
     * @param array  $arOrgIds
     * @param int    $serviceId
     * @return array
     */
    public function getSettingsByOrgIds(string $settingName, array $arOrgIds, int $serviceId)
    {
        return (new Query())->select(['isv.org_id', 'isv.value parent_id', 'isv.id'])
            ->from(IntegrationSettingValue::tableName() . ' as isv')
            ->leftJoin(IntegrationSetting::tableName() . ' as is', 'is.id=isv.setting_id')
            ->where(['is.name' => $settingName, 'isv.org_id' => $arOrgIds, 'is.service_id' => $serviceId])
            ->indexBy('org_id')
            ->all(\Yii::$app->db_api);
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function setMainOrganizations($request)
    {
        $this->validateRequest($request, ['checked', 'main_org', 'service_id']);
        $settingToService = IntegrationSetting::find()->select(['id', 'service_id'])
            ->andWhere(['name' => 'main_org'])
            ->indexBy('service_id')->all();
        $settingId = $settingToService[$request['service_id']]->id;
        $this->resetMainOrgSetting($request);

        $arResult = [];
        foreach ($request['checked'] as $orgId) {
            if ($orgId == $request['main_org']) {
                throw new BadRequestHttpException('setting.main_org_equal_child_org');
            }
            $settingModel = IntegrationSettingValue::findOne(['setting_id' => $settingId, 'org_id' => $orgId]);
            if (!$settingModel) {
                $settingModel = new IntegrationSettingValue();
            }
            $settingModel->setting_id = $settingId;
            $settingModel->org_id = $orgId;
            $settingModel->value = (string)$request['main_org'];
            if (!$settingModel->save()) {
                throw new ValidationException($settingModel->getFirstErrors());
            }
            $arResult[$orgId] = (bool)$settingModel->id;
        }

        return ['result' => $arResult];
    }

    /**
     * Сброс настройки главная организация для всех доступных бизнесов
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function resetMainOrgSetting($request)
    {
        $this->validateRequest($request, ['service_id']);
        $arSettings = $this->getSettingsToOrgByService($request['service_id']);
        $arSettingIds = array_map(function ($el) {
            return $el['id'];
        }, $arSettings['arSettingToOrg']);
        $result = IntegrationSettingValue::updateAll(['value' => ''], ['id' => $arSettingIds]);

        return ['result' => (bool)$result];
    }

    /**
     * Установка бизнеса как дочернего должна обновлять все существующие записи на значения главного бизнеса
     *
     * @param $childOrg
     * @param $mainOrg
     */
    private function updateOuterProductMap($childOrg, $mainOrg): void
    {
        $tableName = DBNameHelper::getApiName() . '.' . OuterProductMap::tableName();
        $query = (new Query())->select([
            'copm.id',
            'popm.outer_product_id',
        ])
            ->from(OuterProductMap::tableName() . ' copm')
            ->leftJoin(OuterProductMap::tableName() . ' popm',
                'popm.vendor_id=copm.vendor_id and popm.product_id=copm.product_id')
            ->where(['copm.organization_id' => $childOrg, 'popm.organization_id' => $mainOrg])
            ->all(\Yii::$app->db_api);
        $data_update = '';
        foreach ($query as $item) {
            $outerProductId = $item['outer_product_id'];
            $id = $item['id'];
            $data_update .= "UPDATE $tableName set outer_product_id = $outerProductId where id=$id;";
        }
        \Yii::$app->db_api->createCommand($data_update)->execute();
    }

    /**
     * Спиосок возможных значения для настроек
     *
     * @param $request
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getItemsSetting($request)
    {
        $this->validateRequest($request, ['service_id', 'setting_name']);

        $setting = IntegrationSetting::findOne(['service_id' => $request['service_id'], 'name' => $request['setting_name']]);

        if (empty($setting)) {
            throw new BadRequestHttpException('setting.not_found');
        }

        switch ($setting->type) {
            case 'input_text':
            case 'password':
                $type = "string";
                break;
            default:
                $type = "json";
        }

        return $this->getItems($setting->item_list, $type);
    }

    /**
     * @param $item_list
     * @param $type
     * @return mixed
     */
    private function getItems($item_list, $type)
    {
        $r = '';
        if ($type == 'json') {
            if (!empty($item_list)) {
                $r = Json::decode($item_list, true);
            } else {
                $r = [];
            }
        }

        return $r;
    }
}