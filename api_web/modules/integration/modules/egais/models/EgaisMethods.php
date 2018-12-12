<?php

namespace api_web\modules\integration\modules\egais\models;

use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\modules\integration\modules\egais\helpers\EgaisHelper;
use api_web\modules\integration\modules\egais\classes\EgaisXmlFiles;
use common\models\egais\EgaisProductOnBalance;
use common\models\egais\EgaisQueryRests;
use common\models\egais\EgaisTypeWriteOff;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;

/**
 * Class EgaisMethods
 *
 * @package api_web\modules\integration\modules\egais\models
 */
class EgaisMethods extends WebApi
{
    /**
     * @param $request
     * @param $orgId
     * @return array
     * @throws BadRequestHttpException|\Exception
     */
     public function setEgaisSettings($request, $orgId)
    {
        if (empty($request['egais_url']) || empty($request['fsrar_id']) || empty($orgId)) {
            throw new BadRequestHttpException('dictionary.request_error');
        }

        $defaultSettings = IntegrationSetting::findAll([
            'service_id' => Registry::EGAIS_SERVICE_ID
        ]);

        if (empty($defaultSettings)) {
            throw new BadRequestHttpException('dictionary.egais_get_setting_error');
        }
        /**@var Transaction $transaction */
        $transaction = \Yii::$app->db_api->beginTransaction();
        foreach ($defaultSettings as $defaultSetting) {
            if (array_key_exists($defaultSetting->name, $request)) {
                $settingValue = IntegrationSettingValue::findOne([
                    'setting_id' => $defaultSetting->id,
                    'org_id' => $orgId
                ]);
                if (!empty($settingValue)) {
                    $settingValue->value = $request[$defaultSetting->name];
                } else {
                    $settingValue = new IntegrationSettingValue([
                        'setting_id' => $defaultSetting->id,
                        'org_id' => $orgId,
                        'value' => $request[$defaultSetting->name],
                    ]);
                }
                if (!$settingValue->save()) {
                    $transaction->rollBack();
                    throw new BadRequestHttpException('dictionary.egais_set_setting_error');
                }
            }
        }
        $transaction->commit();

        return [
            'result' => true
        ];
    }

    public function getWriteOffTypes()
    {
        return EgaisTypeWriteOff::find()->all();
    }

    /**
     * @param array $request
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function getGoodsOnBalance(array $request)
    {
        $orgId = !empty($request['org_id']) ? $request['org_id'] : $this->user->organization_id;

        $setting = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $orgId);
        if (empty($setting)) {
            throw new BadRequestHttpException('dictionary.egais_get_setting_error');
        }

        $existsQuery = EgaisQueryRests::find()
            ->where('org_id = :org_id', [':org_id' => $orgId])
            ->andWhere(['status' => EgaisHelper::QUERY_SENT])
            ->one();

        if (!empty($existsQuery)) {
            $existsQuery->updated_at = \Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $existsQuery->save();
        } else {
            $xml = (new EgaisXmlFiles())->queryRests($setting['fsrar_id']);
            (new EgaisHelper())->sendQueryRests($orgId, $setting['egais_url'], $xml);
        }

        $goods = EgaisProductOnBalance::find()
            ->where('org_id = :org_id', ['org_id' => $orgId])
            ->all();

        return $goods;
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
            throw new BadRequestHttpException('dictionary.request_error');
        }

        $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $this->user->organization_id);

        if (empty($settings)) {
            throw new BadRequestHttpException('dictionary.egais_get_setting_error');
        }

        $return = (new EgaisHelper())->sendActWriteOff($settings['egais_url'], $request['xml'], 'ActWriteOff_v3');

        return [
            'result' => $return
        ];
    }

    /**
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actWriteOn(array $request)
    {
        if (empty($request['xml'])) {
            throw new BadRequestHttpException('dictionary.request_error');
        }

        $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $this->user->organization_id);

        if (empty($settings)) {
            throw new BadRequestHttpException('dictionary.egais_get_setting_error');
        }

        $return = (new EgaisHelper())->sendActWriteOn($settings['egais_url'], $request['xml'], 'ActChargeOn_v2');

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
                throw new BadRequestHttpException('dictionary.egais_get_setting_error');
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
            throw new BadRequestHttpException('dictionary.egais_get_setting_error');
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
            throw new BadRequestHttpException('dictionary.request_error');
        }

        if (!in_array(strtoupper($request['type']), EgaisHelper::$type_document)) {
            throw new BadRequestHttpException('dictionary.egais_type_document_error');
        }

        $orgId = empty($request['org_id'])
            ? $this->user->organization_id
            : $request['org_id'];

        $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $orgId);

        if (empty($settings)) {
            throw new BadRequestHttpException('dictionary.egais_get_setting_error');
        }

        return EgaisHelper::getOneDocument($settings['egais_url'], $request);
    }
}