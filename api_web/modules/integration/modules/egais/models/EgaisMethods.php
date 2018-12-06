<?php

namespace api_web\modules\integration\modules\egais\models;

use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\modules\integration\modules\egais\helpers\EgaisHelper;
use api_web\modules\integration\modules\egais\classes\EgaisXmlFiles;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;
use yii\web\BadRequestHttpException;

class EgaisMethods extends WebApi
{
    /**
     * @param $request
     * @param $orgId
     * @return array
     * @throws BadRequestHttpException
     */
    public function setEgaisSettings($request, $orgId)
    {
        if (empty($request['egais_url']) || empty($request['fsrar_id']) || empty($orgId)) {
            throw new BadRequestHttpException (\Yii::t('api_web', 'dictionary.request_error', ['ru'=>'Ошибка запроса']));
        }

        $defaultSettings = IntegrationSetting::findAll([
            'service_id' => Registry::EGAIS_SERVICE_ID
        ]);

        if (empty($defaultSettings)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'dictionary.egais_get_setting_error', ['ru'=>'Неправильные настройки ЕГАИС']));
        }

        $transaction = \Yii::$app->db_api->beginTransaction();
        foreach ($defaultSettings as $defaultSetting) {
            $settingValue = IntegrationSettingValue::findOne([
                'setting_id' => $defaultSetting->id,
                'org_id' => $orgId
            ]);

            if (!empty($settingValue)) {
                $settingValue->value = $request[$defaultSetting->name];
                $settingValue->updated_at = date('Y-m-d h:i:s');
            } else {
                $settingValue = new IntegrationSettingValue([
                    'setting_id' => $defaultSetting->id,
                    'org_id' => $orgId,
                    'value' => $request[$defaultSetting->name],
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);
            }

            if (!$settingValue->save()) {
                $transaction->rollBack();
                throw new BadRequestHttpException(\Yii::t('api_web', 'dictionary.egais_get_setting_error', ['ru'=>'Неправильные настройки ЕГАИС']));
            }
        }
        $transaction->commit();

        return [
            'result' => true
        ];
    }

    /**
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actWriteOff(array $request)
    {
        if (empty($request['xml'])) {
            throw new BadRequestHttpException (\Yii::t('api_web', 'dictionary.request_error', ['ru'=>'Ошибка запроса']));
        }

        $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $this->user->organization_id);

        if (empty($settings)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'dictionary.egais_get_setting_error', ['ru'=>'Неправильные настройки ЕГАИС']));
        }

        $return = (new EgaisHelper())->sendActWriteOff($settings['egais_url'], $request['xml'], 'ActWriteOff_v3');

        return [
            'result' => $return
        ];
    }

    /**
     * @param $request
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function getQueryRests($request)
    {
        if (empty($request)) {
            $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $this->user->organization_id);

            if (empty($settings)) {
                throw new BadRequestHttpException(\Yii::t('api_web', 'dictionary.egais_get_setting_error', ['ru'=>'Неправильные настройки ЕГАИС']));
            }

            $xml = (new EgaisXmlFiles())->queryRests($settings['fsrar_id']);
            $return = EgaisHelper::sendEgaisQuery($settings['egais_url'], $xml, 'QueryRests');

            return ['result' => $return];
        }

        return ['result' => false];
    }

    /**
     * @param $request
     * @return bool|string|array
     * @throws \Exception
     */
    public function getAllIncomingDoc($request)
    {
        $orgId = empty($request) || empty($request['org_id'])
            ? $this->user->organization_id
            : $request['org_id'];

        $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $orgId);

        if (empty($settings)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'dictionary.egais_get_setting_error', ['ru'=>'Неправильные настройки ЕГАИС']));
        }

        return EgaisHelper::getAllIncomingDoc($settings['egais_url'], $request);
    }

    /**
     * @param $request
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function getOneIncomingDoc($request)
    {
        if (empty($request) || empty($request['type']) || empty($request['id'])) {
            throw new BadRequestHttpException (\Yii::t('api_web', 'dictionary.request_error', ['ru'=>'Ошибка запроса']));
        }

        if (!in_array(strtoupper($request['type']), EgaisHelper::$type_document)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'dictionary.egais_type_document_error', ['ru'=>'Неправильный тип документа ЕГАИС']));
        }

        $orgId = empty($request['org_id'])
            ? $this->user->organization_id
            : $request['org_id'];

        $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $orgId);

        if (empty($settings)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'dictionary.egais_get_setting_error', ['ru'=>'Неправильные настройки ЕГАИС']));
        }

        return EgaisHelper::getOneDocument($settings['egais_url'], $request);
    }
}