<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 8/30/2018
 * Time: 4:25 PM
 */

namespace api_web\modules\integration\modules\vetis\helpers;

use api\common\models\merc\MercVsd;
use api_web\classes\UserWebApi;
use api_web\components\Registry;
use api_web\helpers\WebApiHelper;
use common\helpers\DBNameHelper;
use common\models\IntegrationSettingValue;
use api_web\modules\integration\modules\vetis\api\cerber\cerberApi;
use common\models\vetis\VetisCountry;
use common\models\vetis\VetisProductByType;
use common\models\vetis\VetisSubproductByProduct;
use common\models\vetis\VetisUnit;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Класс для работы с ВСД
 * */
class VetisHelper
{
    /**@var MercVsd model */
    private $vsdModel;
    /**@var int organization id */
    private $orgId;
    /**@var array $expertizeList расшифровки статусов экспертиз */
    public static $expertizeList = [
        'UNKNOWN'     => 'the_result_is_unknown', //Результат неизвестен
        'UNDEFINED'   => 'the_result_can_not_be_determined', //Результат невозможно определить (не нормируется)
        'POSITIVE'    => 'positive_result', //Положительный результат
        'NEGATIVE'    => 'negative_result', //Отрицательный результат
        'UNFULFILLED' => 'not_conducted', //Не проводилось
        'VSERAW'      => 'VSE_subjected_the_raw_materials_from_which_the_products_were_manufactured', // ВСЭ подвергнуто сырьё, из которого произведена продукция
        'VSEFULL'     => 'the_products_are_fully', // Продукция подвергнута ВСЭ в полном объеме
    ];
    /**@var array $ordersStatuses статусы для заказов */
    public static $ordersStatuses = [
        'WITHDRAWN' => 'vsd_status_withdrawn', //'Сертификаты аннулированы',
        'CONFIRMED' => 'vsd_status_confirmed', //'Сертификаты ожидают погашения',
        'UTILIZED'  => 'vsd_status_utilized', //'Сертификаты погашены',
    ];

    /**
     * VetisHelper constructor.
     */
    public function __construct()
    {
        $this->orgId = \Yii::$app->user->identity->organization_id;
    }

    /**
     * Получение краткой информации о ВСД
     *
     * @param string $uuid
     * @throws BadRequestHttpException
     * @return VetisHelper
     * */
    public function getShortInfoVsd($uuid)
    {
        $this->uuid = $uuid;

        $this->vsdModel = MercVsd::findOne(['uuid' => $uuid]);
        if (!$this->vsdModel) {
            throw new BadRequestHttpException('Uuid is bad');
        }
        $this->producer_name = $this->vsdModel->producer_name;
        $confirmedBy = json_decode($this->vsdModel->confirmed_by, true);
        $this->confirmed_by = [
            'fio'  => $confirmedBy['fio'] ?? "-",
            'post' => $confirmedBy['post'] ?? "",
        ];
        $country = VetisCountry::findOne(['guid' => $this->vsdModel->origin_country_guid]);
        $this->country_name = isset($country) ? $country->name : null;

        $transportInfo = json_decode($this->vsdModel->transport_info, true);
        $this->vehicle_number = isset($transportInfo['transportNumber']['vehicleNumber']) ? $transportInfo['transportNumber']['vehicleNumber'] : null;
        $other = json_decode($this->vsdModel->other_info, true);
        if (isset($other['cargoExpertized'])) {
            $this->cargo_expertized = \Yii::t('api_web', self::$expertizeList[$other['cargoExpertized']]);
        }
        $this->location_prosperity = $other['locationProsperity'];
        $this->special_marks = $other['specialMarks'];
        $this->issueNumber = (isset($this->vsdModel->number)) ? $this->vsdModel->number : null;
        $this->issueDate = WebApiHelper::asDatetime($this->vsdModel->date_doc);
        $this->form = $this->vsdModel->form;
        $this->type = MercVsd::$types[$this->vsdModel->type];
        $this->status = $this->vsdModel->status;
        $this->recipient_name = $this->vsdModel->recipient_name;
        $this->sender_name = $this->vsdModel->sender_name;
        return $this;
    }

    /**
     * Получение полной информации о ВСД
     *
     * @param $uuid
     * @throws \Exception
     * @return VetisHelper
     * */
    public function getFullInfoVsd($uuid)
    {
        $this->getShortInfoVsd($uuid);

        $hc = cerberApi::getInstance()->getEnterpriseByGuid($this->vsdModel->sender_guid);
        if (isset($hc)) {
            if (isset($hc->owner)) {
                $hc = cerberApi::getInstance()->getBusinessEntityByGuid($hc->owner->guid);
            }
        }
        $this->consignor_business = isset($hc) ? $hc->name . ', ИНН:' . $hc->inn : null;
        $this->product_type = isset($this->vsdModel->product_type) ?
            MercVsd::$product_types[$this->vsdModel->product_type] : null;
        $product = VetisProductByType::findOne(['guid' => $this->vsdModel->product_guid]);
        $this->product = isset($product) ? $product->name : null;
        $sub_product = VetisSubproductByProduct::findOne(['guid' => $this->vsdModel->sub_product_guid]);;
        $this->sub_product = isset($sub_product) ? $sub_product->name : null;

        $this->product_in_numenclature = $this->vsdModel->product_name ?? null;

        $unit = VetisUnit::findOne(['guid' => $this->vsdModel->unit_guid]);;
        $this->volume = $this->vsdModel->amount . (isset($unit) ? " " . $unit->name : '');

        $this->date_of_production = WebApiHelper::asDatetime($this->vsdModel->production_date);
        $this->expiry_date_of_production = WebApiHelper::asDatetime($this->vsdModel->expiry_date);
        $this->perishable_products = isset($this->vsdModel->perishable) ? (($this->vsdModel->perishable == 'true') ? 'Да' :
            'Нет') : null;

        $laboratory_research = json_decode($this->vsdModel->laboratory_research, true);
        $this->expertiseInfo = 'Экспертиза не проводилась';
        try {
            if (array_key_exists('batchID', $laboratory_research)) {
                $this->expertiseInfo = mb_convert_encoding(
                    $laboratory_research['indicator']['name'] . ' : ' . $laboratory_research['operator']['name'] . " эксп №" . $laboratory_research['expertiseID'] . " от " . date("Y-m-d h:i:s", strtotime($laboratory_research['actualDateTime'])) . " ( " . $laboratory_research['conclusion'] . " )", "UTF-8", "UTF-8");
            } else {
                $arTmp = [];
                foreach ($laboratory_research as $item) {
                    $arTmp[] = mb_convert_encoding(
                        $item['indicator']['name'] . ' : ' . $item['operator']['name'] . " эксп №" . $item['expertiseID'] . " от " . WebApiHelper::asDatetime($item['actualDateTime']) . " ( " . $item['conclusion'] . " )", "UTF-8", "UTF-8");
                }
                $this->expertiseInfo = $arTmp;
            }
        } catch (\Throwable $t) {
            // too many errors in VSD
        }

        $transportInfo = json_decode($this->vsdModel->transport_info, true);

        $this->transport_type = isset($transportInfo['transportType']) ? MercVsd::$transport_types[$transportInfo['transportType']] : null;
        $this->containerNumber = isset($transportInfo['transportNumber']['containerNumber']) ? $transportInfo['transportNumber']['containerNumber'] : null;
        $this->agonNumber = isset ($transportInfo['transportNumber']['agonNumber']) ? $transportInfo['transportNumber']['wagonNumber'] : null;
        $this->vehicleNumber = isset($transportInfo['transportNumber']['vehicleNumber']) ? $transportInfo['transportNumber']['vehicleNumber'] : null;
        $this->trailerNumber = isset($transportInfo['transportNumber']['trailerNumber']) ? $transportInfo['transportNumber']['trailerNumber'] : null;
        $this->shipName = isset($transportInfo['transportNumber']['shipName']) ? $transportInfo['transportNumber']['shipName'] : null;
        $this->flightNumber = isset($transportInfo['transportNumber']['flightNumber']) ? $transportInfo['transportNumber']['flightNumber'] : null;
        $this->transport_storage_type = isset($this->vsdModel->transport_storage_type) ? MercVsd::$storage_types[$this->vsdModel->transport_storage_type] : null;

        $confirmed_by = json_decode($this->vsdModel->confirmed_by, true);

        if (is_array($confirmed_by)) {
            $specPerson = current($confirmed_by);
        } else {
            $specPerson = $confirmed_by;
        }
        $this->specified_person = $specPerson['fio'] ?? "-";
        $this->specified_person_post = $specPerson['post'] ?? "-";

        $this->waybillSeries = $this->vsdModel->waybill_number;
        $this->waybillDate = WebApiHelper::asDatetime($this->vsdModel->waybill_date);
        return $this;
    }

    /**
     * @param      $param
     * @param null $default
     * @return null
     */
    public function isSetDef($param, $default = null)
    {
        if (isset($param) && !empty($param)) {
            return $param;
        }
        return $default;
    }

    /**
     * @param $var
     * @param $arParams
     * @param $arLabels
     * @return array
     */
    public function set(&$var, $arParams, $arLabels)
    {
        $arGoodParams = [];
        foreach ($arLabels as $label) {
            if (isset($arParams[$label]) && !empty($arParams[$label])) {
                if ($label == 'date') {
                    $this->set($var, $arParams[$label], ['from', 'to']);
                } else {
                    $var->{$label} = $arParams[$label];
                }
                $arGoodParams[$label] = $arParams[$label];
            }
        }

        return $arGoodParams;
    }

    /**
     * @param int   $id
     * @param array $uuids
     * @return array|bool
     */
    public function getGroupInfo(int $id, $uuids)
    {
        $tableName = $this->getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
        $query = (new Query())
            ->select(
                [
                    'COUNT(oc.id) as count',
                    'o.created_at',
                    'o.total_price',
                    'vendor.name as vendor_name',
                    'GROUP_CONCAT(DISTINCT `m`.`status` SEPARATOR \',\') AS `statuses`',
                ]
            )
            ->from('order o')
            ->innerJoin('order_content oc', 'oc.order_id = o.id')
            ->innerJoin('organization vendor', 'o.vendor_id = vendor.id')
            ->leftJoin('`' . $tableName . '`.merc_vsd m', 'm.uuid = oc.merc_uuid COLLATE utf8_unicode_ci')
            ->where(['o.id' => $id])
            ->andWhere('oc.merc_uuid is not null')
            ->andWhere(['m.uuid' => $uuids])
            ->one(\Yii::$app->db);

        if (!is_null($query['statuses'])) {
            $query['statuses'] = $this->getStatusForGroup($query['statuses']);
        }

        if ($query['count'] == 0) {
            return null;
        }

        return $query;
    }

    /**
     * Get database name from config
     *
     * @param string $name
     * @param string $dsn dsn string from config
     * @return string database name
     * */
    public function getDsnAttribute($name, $dsn)
    {
        return DBNameHelper::getDsnAttribute($name, $dsn);
    }

    /**
     * Get group status from array statuses
     *
     * @param string $strStatuses
     * @return array
     * */
    public function getStatusForGroup($strStatuses)
    {
        $statuses = explode(',', $strStatuses);
        if (count($statuses) > 1) {
            return [
                'id'   => 'CONFIRMED',
                'text' => \Yii::t('api_web', self::$ordersStatuses['CONFIRMED'])
            ];
        } else {
            $status = current($statuses);
            if ($status) {
                return [
                    'id'   => $status,
                    'text' => \Yii::t('api_web', self::$ordersStatuses[$status])
                ];
            }
        }
    }

    /**
     * @param       $models
     * @param array $order_ids
     * @return array
     */
    public function attachModelsInDocument($models, array $order_ids)
    {
        $tableName = $this->getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
        $query = (new Query())
            ->select("
                `m`.uuid,
                `m`.`sender_name`,
                `m`.`product_name`,
                `m`.`status`,
                `m`.`last_update_date` as status_date,
                `m`.`amount`,
                `m`.`unit`,
                `m`.`production_date`,
                `m`.`date_doc`,
                `o`.id as document_id
            ")
            ->from('order o')
            ->leftJoin('order_content oc', 'oc.order_id = o.id')
            ->leftJoin('`' . $tableName . '`.merc_vsd m', 'm.uuid = oc.merc_uuid')
            ->where(['in', 'o.id', $order_ids])
            ->andWhere('oc.merc_uuid is not null')
            ->all();

        $query = ArrayHelper::index($query, 'uuid');

        $models = ArrayHelper::merge($models, $query);

        return $models;
    }

    /**
     * @param $uuids
     * @return array|\yii\db\ActiveRecord[]
     * @throws \Exception
     */
    public function getAvailableVsd($uuids)
    {
        $orgIds = (new UserWebApi())->getUserOrganizationBusinessList();
        $arOrgIds = array_map(function ($el) {
            return $el['id'];
        }, $orgIds['result']);

        return MercVsd::find()->select(['uuid', 'recipient_guid', 'sender_guid'])
            ->leftJoin('merc_pconst mc', 'mc.const_id=10 and mc.value=merc_vsd.recipient_guid')
            ->where(['mc.org' => $arOrgIds])
            ->andWhere(['uuid' => $uuids])->indexBy('uuid')->all();
    }

    /**
     * @param null $enterpriseGuids
     * @return array
     * @throws \Exception
     */
    public function getNotConfirmedVsd($enterpriseGuids = null)
    {
        if (!$enterpriseGuids) {
            $enterpriseGuids = $this->getEnterpriseGuids();
        }
        $query = (new Query())->select(['GROUP_CONCAT(uuid) as uuids', 'COUNT(*) as count'])->from('merc_vsd')
            ->where(['status' => 'CONFIRMED', 'recipient_guid' => $enterpriseGuids])->one(\Yii::$app->db_api);

        return [
            'uuids' => explode(',', $query['uuids']),
            'count' => $query['count'],
        ];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getEnterpriseGuids()
    {
        $enterpriseGuids = [];
        $orgIds = (new UserWebApi())->getUserOrganizationBusinessList();
        foreach ($orgIds['result'] as $orgId) {
            $entGuid = $this->getSettings($orgId['id'], ['enterprise_guid']);
            if (strlen($entGuid) < 36) {
                continue;
            }
            $enterpriseGuids[$entGuid] = $entGuid;
        }
        return $enterpriseGuids;
    }

    /**
     * @param $userStatus
     * @param $uuid
     * @return int
     */
    public function setMercVsdUserStatus($userStatus, $uuid)
    {
        $where = ['uuid' => $uuid];
        return MercVsd::updateAll(['user_status' => $userStatus, 'last_error' => new Expression('NULL')], $where);
    }

    /**
     * @return \frontend\modules\clientintegr\modules\merc\components\VsdHttp
     * @throws \Exception
     */
    public function generateVsdHttp()
    {
        $settings = $this->getSettings($this->orgId, ['vetis_login', 'vetis_password', 'issuer_id']);
        return new \frontend\modules\clientintegr\modules\merc\components\VsdHttp([
            'authLink'       => \Yii::$app->params['vtsHttp']['authLink'],
            'vsdLink'        => \Yii::$app->params['vtsHttp']['vsdLink'],
            'pdfLink'        => \Yii::$app->params['vtsHttp']['pdfLink'],
            'chooseFirmLink' => \Yii::$app->params['vtsHttp']['chooseFirmLink'],
            'username'       => $settings["vetis_login"],
            'password'       => $settings["vetis_password"],
            'firmGuid'       => $settings["issuer_id"],
        ]);
    }

    /**
     * @param $error
     * @param $uuid
     * @return int
     */
    public function setLastError($error, $uuid)
    {
        if (mb_strlen($error) > 255) {
            $error = mb_substr($error, 0, 254);
        }
        $where = ['uuid' => $uuid];
        return MercVsd::updateAll(['last_error' => $error, 'user_status' => 'operation error'], $where);
    }

    /**
     * @param $uuid
     * @param $orgId
     * @return string
     */
    public function getVsdDirection($uuid, $orgId)
    {
        $guid = $this->getSettings($orgId, ['enterprise_guid']);
        $model = MercVsd::findOne(['uuid' => $uuid]);
        if ($guid == $model->recipient_guid && $guid != $model->sender_guid) {
            return 'incoming';
        }

        return 'outgoing';
    }

    /**
     * @param       $orgId
     * @param array $settingNames
     * @return array|string
     */
    public function getSettings($orgId, $settingNames = [])
    {
        return IntegrationSettingValue::getSettingsByServiceId(Registry::MERC_SERVICE_ID,
            $orgId, $settingNames);
    }
}