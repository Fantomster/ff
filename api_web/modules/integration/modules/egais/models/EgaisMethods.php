<?php

namespace api_web\modules\integration\modules\egais\models;

use api_web\components\definitions\egais\actWriteOff\ActWriteOffV3;
use api_web\components\definitions\egais\actWriteOn\ActChargeOnV2;
use api_web\components\Registry;
use api_web\components\ValidateRequest;
use api_web\components\WebApi;
use api_web\modules\integration\modules\egais\helpers\EgaisHelper;
use api_web\modules\integration\modules\egais\classes\EgaisXmlFiles;
use common\models\egais\EgaisProductOnBalance;
use common\models\egais\EgaisQueryRests;
use common\models\egais\EgaisTypeWriteOff;
use common\models\egais\EgaisWriteOffHistory;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class EgaisMethods
 *
 * @package api_web\modules\integration\modules\egais\models
 */
class EgaisMethods extends WebApi
{
    private $cronHelper;

    public function __construct()
    {
        parent::__construct();
        $this->cronHelper = new EgaisCronHelper();
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \api_web\exceptions\ValidationException
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function setEgaisSettings($request)
    {
        $this->validateRequest($request, ["egais_url", "fsrar_id"]);
        $orgId = $this->user->organization_id;
        $userId = $this->user->id;

        $settingList = (new Query())
            ->from(["iss" => IntegrationSetting::tableName()])
            ->select([
                "setting_id" => "iss.id",
                "isv.id",
                "iss.name",
                "isv.org_id",
                "isv.value"
            ])
            ->leftJoin([
                "isv" => IntegrationSettingValue::tableName()
            ], "isv.setting_id = iss.id AND isv.org_id = :org_id", [
                ":org_id" => $orgId
            ])
            ->where([
                "iss.service_id" => Registry::EGAIS_SERVICE_ID,
            ])
            ->all(\Yii::$app->db_api);

        if (empty($settingList)) {
            throw new BadRequestHttpException("dictionary.egais_get_setting_error");
        }

        /**@var Transaction $transaction */
        $transaction = \Yii::$app->db_api->beginTransaction();
        $isSave = true;
        $errorMessage = "";

        foreach ($settingList as $setting) {
            $fieldList = [
                "setting_id" => $setting["setting_id"],
                "org_id"     => $orgId,
                "value"      => $request[$setting["name"]]
            ];

            if (is_null($setting["id"])) {
                $settingValue = new IntegrationSettingValue($fieldList);
                $settingValue->setAttributes($fieldList);
            } else {
                /** @var IntegrationSettingValue $settingValue */
                $settingValue = \Yii::createObject([
                    "class"      => IntegrationSettingValue::class,
                    "id"         => $setting["id"],
                    "org_id"     => $fieldList["org_id"],
                    "value"      => $fieldList["value"],
                    "setting_id" => $fieldList["setting_id"],
                ]);

                $settingValue->setOldAttributes([
                    "id" => $setting["id"],
                ]);
            };

            if (!$settingValue->save()) {
                $isSave = false;
                $errorMessage = print_r($settingValue->getFirstErrors(), true);
                break;
            }
        }

        if ($isSave) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
            $this->cronHelper->writeInJournal([
                "message" => $errorMessage,
                "code"    => EgaisHelper::SAVE_SETTING_ERROR,
                "org_id"  => $orgId,
                "user_id" => $userId
            ]);

            throw new BadRequestHttpException("dictionary.egais_set_setting_error");
        }

        return [
            "result" => true
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
     * @throws \api_web\exceptions\ValidationException
     * @throws \yii\base\InvalidConfigException
     */
    public function getGoodsOnBalance(array $request)
    {
        $orgId = !empty($request["org_id"]) ? $request["org_id"] : $this->user->organization_id;

        $setting = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $orgId);
        if (empty($setting)) {
            throw new BadRequestHttpException("dictionary.egais_get_setting_error");
        }

        $existsQuery = EgaisQueryRests::find()
            ->where("org_id = :org_id", [":org_id" => $orgId])
            ->andWhere(["status" => EgaisHelper::QUERY_SENT])
            ->one();

        if (!empty($existsQuery)) {
            $existsQuery->updated_at = \Yii::$app->formatter->asDate(time(), "yyyy-MM-dd HH:mm:ss");
            $existsQuery->save();
        } else {
            $xml = (new EgaisXmlFiles())->queryRests($setting["fsrar_id"]);
            (new EgaisHelper())->sendQueryRests($orgId, $setting["egais_url"], $xml);
        }

        $goods = EgaisProductOnBalance::find()
            ->where("org_id = :org_id", ["org_id" => $orgId])
            ->all();

        return $goods;
    }

    /**
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \api_web\exceptions\ValidationException
     */
    public function actWriteOff(array $request)
    {
        ValidateRequest::loadData(ActWriteOffV3::class, $request);

        $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $this->user->organization_id);

        if (empty($settings)) {
            throw new BadRequestHttpException("dictionary.egais_get_setting_error");
        }

        $return = (new EgaisHelper())->sendActWriteOff($settings, $request, "ActWriteOff_v3");

        return [
            "result" => $return
        ];
    }

    /**
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \api_web\exceptions\ValidationException
     */
    public function actWriteOn(array $request)
    {
        ValidateRequest::loadData(ActChargeOnV2::class, $request);

        $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $this->user->organization_id);

        if (empty($settings)) {
            throw new BadRequestHttpException("dictionary.egais_get_setting_error");
        }

        $return = (new EgaisHelper())->sendActWriteOn($settings, $request);

        return [
            "result" => $return
        ];
    }

    /**
     * @param $request
     * @return bool|string|array
     * @throws \Exception
     */
    public function getAllIncomingDoc($request)
    {
        $request["user_id"] = $this->user->id;
        $request["org_id"] = $request["org_id"] ?? $this->user->organization_id;

        $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $request["org_id"]);

        if (empty($settings)) {
            throw new BadRequestHttpException("dictionary.egais_get_setting_error");
        }

        return (new EgaisHelper())->getAllIncomingDoc($settings["egais_url"], $request);
    }

    /**
     * @param $request
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \api_web\exceptions\ValidationException
     */
    public function getOneIncomingDoc($request)
    {
        if (empty($request) || empty($request["type"]) || empty($request["id"])) {
            throw new BadRequestHttpException("dictionary.request_error");
        }

        if (!in_array(strtoupper($request["type"]), EgaisHelper::$type_document)) {
            throw new BadRequestHttpException("dictionary.egais_type_document_error");
        }

        $orgId = empty($request["org_id"])
            ? $this->user->organization_id
            : $request["org_id"];

        $settings = IntegrationSettingValue::getSettingsByServiceId(Registry::EGAIS_SERVICE_ID, $orgId);

        if (empty($settings)) {
            throw new BadRequestHttpException("dictionary.egais_get_setting_error");
        }

        return (new EgaisCronHelper())->getOneIncomingDoc($settings["egais_url"], $request);
    }

    /**
     * @param array $request
     * @return EgaisProductOnBalance
     * @throws BadRequestHttpException
     */
    public function getProductBalanceInfo(array $request)
    {
        $this->validateRequest($request, ["alc_code"]);

        $product = EgaisProductOnBalance::findOne([
            "org_id"   => $this->user->organization_id,
            "alc_code" => $request["alc_code"]
        ]);

        if (empty($product)) {
            throw new BadRequestHttpException("dictionary.egais_get_product");
        }

        return $product;
    }

    /**
     * @param array $request
     * @return array
     */
    public function getWrittenOffProductList(array $request)
    {
        if (isset($request["date"])) {
            $dateStart = isset($request["date"]["start"])
                ? $this->formattedDate("{$request["date"]["start"]} 00:00:00")
                : "";

            $dateEnd = isset($request["date"]["end"])
                ? $this->formattedDate("{$request["date"]["end"]} 23:59:59")
                : "";
        }

        $writtenOffProductList = EgaisWriteOffHistory::find()
            ->where([
                "org_id" => $this->user->organization_id
            ])
            ->andFilterWhere([
                "BETWEEN",
                "created_at",
                $dateStart ?? "",
                $dateEnd ?? ""
            ])
            ->orderBy(["created_at" => SORT_DESC])
            ->all();

        $page = $request["pagination"]["page"] ?? 1;
        $pageSize = $request["pagination"]["page_size"] ?? 12;

        $dataProvider = new ArrayDataProvider([
            "allModels"  => ArrayHelper::getColumn($writtenOffProductList, function (EgaisWriteOffHistory $product) {
                return $product->prepareProduct();
            }),
            "pagination" => [
                "page"     => $page - 1,
                "pageSize" => $pageSize,
            ],
        ]);

        $totalPage = ceil($dataProvider->totalCount / $pageSize) ?? 0;

        return [
            "items"      => $dataProvider->models ?? [],
            "pagination" => [
                "page"       => $page,
                "page_size"  => $pageSize,
                "total_page" => $totalPage
            ]
        ];
    }

    /**
     * @param string $date
     * @return string
     */
    private function formattedDate(string $date): string
    {
        $formattedDate = \DateTime::createFromFormat("d.m.Y H:i:s", $date);
        if ($formattedDate) {
            return $formattedDate->format("Y-m-d H:i:s");
        }

        return "";
    }
}