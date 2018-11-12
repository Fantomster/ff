<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;
use yii\db\Query;
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
            ->select(['name', 'value'])
            ->leftJoin(
                IntegrationSetting::tableName(),
                IntegrationSetting::tableName() . ".id = " . IntegrationSettingValue::tableName() . ".setting_id"
            )->where(
                "org_id = :org and service_id = :service and is_active = true", [
                ':org'     => $this->user->organization_id,
                ':service' => $post['service_id']
            ])
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
        $modelSetting = IntegrationSetting::findOne(['name' => $request['name'], 'service_id' => $service_id, 'is_active' => true]);
        if (!$modelSetting) {
            throw new BadRequestHttpException('integration_setting.not_found');
        }

        $model = IntegrationSettingValue::find()
            ->where(
                "setting_id = :setting_id and org_id = :org",
                [
                    ':setting_id' => $modelSetting->id,
                    ':org'        => $this->user->organization_id
                ]
            )
            ->one();

        if (!$model) {
            $model = new IntegrationSettingValue();
            $model->setting_id = $modelSetting->id;
            $model->org_id = $this->user->organization_id;
        }

        $model->value = $request['value'];

        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }

        return $model->value;
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
        $arOrgs = $this->container->get('UserWebApi')->getUserOrganizationBusinessList();
        $arOrgIds = array_map(function ($el) {
            return (int)$el['id'];
        }, $arOrgs['result']);
        $arSettingToOrg = $this->getSettingsByOrgIds('main_org', $arOrgIds, $request['service_id']);

        return ['result' => array_map(function ($el) use ($arSettingToOrg) {
            $setting = $arSettingToOrg[$el['id']] ?? null;
            $el['main_org'] = $el['checked'] = false;
            if (!is_null($setting)) {
                if ($setting['parent_id'] === "") {
                    $el['main_org'] = true;
                } else {
                    $el['checked'] = true;
                }
            }
            $el['parent_id'] = $setting['parent_id'] != "" ? $setting['parent_id'] : null;
            return $el;
        }, $arOrgs['result'])];
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
        return (new Query())->select(['isv.org_id', 'isv.value parent_id'])
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
     */
    public function setMainOrganizations($request){
        $this->validateRequest($request, ['checked', 'main_org', 'service_id']);
        $settingToService = IntegrationSetting::find()->select(['id', 'service_id'])
            ->andWhere(['name' => 'main_org'])
            ->indexBy('service_id')->all();
        $settingId = $settingToService[$request['service_id']]->id;

        $arResult = [];
        foreach ($request['checked'] as $orgId) {
            $settingModel = IntegrationSettingValue::findOne(['setting_id' => $settingId, 'org_id' => $orgId]);
            if (!$settingModel){
                $settingModel = new IntegrationSettingValue();
            }
            $settingModel->setting_id = $settingId;
            $settingModel->org_id = $orgId;
            $settingModel->value = (string)$request['main_org'];
            if (!$settingModel->save()){
                throw new ValidationException($settingModel->getFirstErrors());
            }
            $arResult[$orgId] = (bool)$settingModel->id;
        }

        return ['result' => $arResult];
    }
}