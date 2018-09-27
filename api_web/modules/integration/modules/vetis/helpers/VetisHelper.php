<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 8/30/2018
 * Time: 4:25 PM
 */

namespace api_web\modules\integration\modules\vetis\helpers;

use api\common\models\merc\MercVsd;
use common\helpers\DBNameHelper;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use yii\db\Query;
use frontend\modules\clientintegr\modules\merc\helpers\api\ikar\ikarApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Класс для работы с ВСД
 * */
class VetisHelper
{
    /**@var object Vetis raw document */
    private $doc;
    /**@var MercVsd model */
    private $vsdModel;
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

    public function __construct()
    {
        $this->org_id = \Yii::$app->user->identity->organization_id;
    }

    /**
     * Получение краткой информации о ВСД
     * @param string $uuid
     * @throws BadRequestHttpException
     * */
    public function getShortInfoVsd($uuid)
    {
        $this->uuid = $uuid;
        $this->doc = mercuryApi::getInstance()->getVetDocumentByUUID($uuid);
        if (!$this->doc) {
            throw new BadRequestHttpException('Uuid is bad');
        }
        $this->vsdModel = MercVsd::findOne(['uuid' => $uuid]);
        $arProducerName = unserialize($this->vsdModel->producer_name);
        $this->producer_name = is_array($arProducerName) ? reset($arProducerName) : $arProducerName;
        $country_raw = ikarApi::getInstance($this->org_id)->getCountryByGuid($this->doc->certifiedConsignment->batch->origin->country->guid);
        $this->country_name = isset($country_raw) ? $country_raw->name : null;
        if (isset($this->doc->referencedDocument)) {
            $this->setTransportWaybill($this->doc->referencedDocument);
        }
        $this->cargo_expertized = isset($this->doc->authentication->cargoExpertized) ?
            \Yii::t('api_web', self::$expertizeList[$this->doc->authentication->cargoExpertized]) : null;
        $this->location_prosperity = $this->doc->authentication->locationProsperity;
        $this->specialMarks = $this->doc->authentication->specialMarks ?? null;
        $this->vehicle_number = $this->vsdModel->vehicle_number;

        return $this;
    }

    /**
     * Получение полной информации о ВСД
     * @param $uuid
     * @throws BadRequestHttpException
     * */
    public function getFullInfoVsd($uuid)
    {
        $this->getShortInfoVsd($uuid);

        $businessEntity = cerberApi::getInstance($this->org_id)->getBusinessEntityByGuid($this->doc->certifiedConsignment->consignor->businessEntity->guid);
        $this->consignor_business = isset($businessEntity) ? $businessEntity->name . ', ИНН:' . $businessEntity->inn : null;
        $this->product_type = isset($this->doc->certifiedConsignment->batch->productType) ?
            MercVsd::$product_types[$this->doc->certifiedConsignment->batch->productType] : null;
        $product_raw = productApi::getInstance($this->org_id)->getProductByGuid($this->doc->certifiedConsignment->batch->product->guid);
        $this->product = isset($product_raw) ? $product_raw->name : null;
        $sub_product_raw = productApi::getInstance($this->org_id)->getSubProductByGuid($this->doc->certifiedConsignment->batch->subProduct->guid);
        $this->sub_product = isset($sub_product_raw) ? $sub_product_raw->name : null;
        $this->product_in_numenclature = $this->doc->certifiedConsignment->batch->productItem->name ?? null;
        $unit = dictsApi::getInstance($this->org_id)->getUnitByGuid($this->doc->certifiedConsignment->batch->unit->guid);
        $this->volume = $this->doc->certifiedConsignment->batch->volume . (isset($unit) ? " " . $unit->name : '');
        $this->date_of_production = MercVsd::getDate($this->doc->certifiedConsignment->batch->dateOfProduction);
        $this->expiry_date_of_production = MercVsd::getDate($this->doc->certifiedConsignment->batch->expiryDate);
        $this->perishable_products = isset($this->doc->certifiedConsignment->batch->perishable) ?
            (($this->doc->certifiedConsignment->batch->perishable == 'true') ? 'Да' : 'Нет') : null;
        $producer = isset($this->doc->certifiedConsignment->batch->origin->producer) ?
            MercVsd::getProduccerData($this->doc->certifiedConsignment->batch->origin->producer, $this->org_id) : null;
        $this->producers = isset($producer) ? implode(", ", $producer['name']) : null;
        $labResearch = $this->doc->authentication->laboratoryResearch;
        $this->expertiseInfo = 'Экспертиза не проводилась';
        try {
            if (isset($labResearch)) {
                $this->expertiseInfo = $labResearch->operator->name . ' эксп №' . $labResearch->expertiseID . ' от ' .
                    $labResearch->referencedDocument->issueDate . ' ( ' . $labResearch->indicator->name . ' - ' .
                    $labResearch->conclusion . ' )';
            }
        } catch (\Throwable $t) {
            // too many errors in VSD
        }
        $this->transport_type = isset($this->doc->certifiedConsignment->transportInfo->transportType) ?
            MercVsd::$transport_types[$this->doc->certifiedConsignment->transportInfo->transportType] : null;
        $this->transport_number = $this->doc->certifiedConsignment->transportInfo->transportNumber->vehicleNumber ?? null;
        $this->transport_storage_type = isset($this->doc->certifiedConsignment->transportStorageType) ? MercVsd::$storage_types[$this->doc->certifiedConsignment->transportStorageType] : null;
        if (is_array($this->doc->statusChange)) {
            $specPerson = current($this->doc->statusChange);
        } else {
            $specPerson = $this->doc->statusChange;
        }
        $this->specified_person = $specPerson->specifiedPerson->fio ?? "-";
        $this->specified_person_post = $specPerson->specifiedPerson->post ?? "";

        return $this;
    }

    /**
     * Парсит $doc->referencedDocument и записывает в экземпляр класса
     * @param object $refDoc
     * */
    public function setTransportWaybill($refDoc): void
    {
        $docs = [];
        if (!is_array($refDoc)) {
            $docs[] = $refDoc;
        } else {
            $docs = $refDoc;
        }
        $this->referenced_document = null;
        $this->referenced_date = null;
        foreach ($docs as $item) {
            if (($item->type >= 1) && ($item->type <= 5)) {
                $str = '';
                $str .= isset($item->issueSeries) && !empty($item->issueSeries) ? $item->issueSeries . ' ' : '';
                $str .= $item->issueNumber;
                $this->referenced_document = $str;
                $this->referenced_date = $item->issueDate;
                break;
            }
        }
    }

    public function isSetDef($param, $default = null)
    {
        if (isset($param) && !empty($param)) {
            return $param;
        }
        return $default;
    }

    public function set(&$var, $arParams, $arLabels)
    {
        $arGoodParams = [];
        foreach ($arLabels as $label) {
            if (isset($arParams[$label]) && !empty($arParams[$label])) {
                if ($label == 'date') {
                    $this->set($var, $arParams[$label], ['from', 'to']);
                } else {
                    $var->{$label} = $arParams[$label];
                    $arGoodParams[$label] = $arParams[$label];
                }
            }
        }

        return $arGoodParams;
    }

    /**
     * @param int $id
     * @return array|bool
     */
    public function getGroupInfo(int $id)
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
            ->leftJoin('order_content oc', 'oc.order_id = o.id')
            ->leftJoin('organization vendor', 'o.vendor_id = vendor.id')
            ->leftJoin('`' . $tableName . '`.merc_vsd m', 'm.uuid = oc.merc_uuid COLLATE utf8_unicode_ci')
            ->where(['o.id' => $id])
            ->andWhere('oc.merc_uuid is not null')
            ->one(\Yii::$app->db);

        $query['statuses'] = $this->getStatusForGroup($query['statuses']);

        return $query;
    }

    /**
     * @return Query
     */
    public function getListQuery()
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
            ->from('`' . $tableName . '`.merc_vsd m')
            ->leftJoin('order_content oc', 'oc.merc_uuid = m.uuid COLLATE utf8_unicode_ci')
            ->leftJoin('order o', 'o.id = oc.order_id')
            ->orderBy(['m.date_doc' => SORT_DESC]);

        return $query;
    }

    /**
     * Get database name from config
     * @param string $name
     * @param string $dsn dsn string from config
     * @return string database name
     * */
    private function getDsnAttribute($name, $dsn)
    {
        return DBNameHelper::getDsnAttribute($name, $dsn);
    }

    /**
     * Get group status from array statuses
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
            return [
                'id'   => $status,
                'text' => \Yii::t('api_web', self::$ordersStatuses[$status])
            ];
        }
    }

    /**
     * @param $models
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
            ->leftJoin('`' . $tableName . '`.merc_vsd m', 'm.uuid = oc.merc_uuid COLLATE utf8_unicode_ci')
            ->where(['in', 'o.id', $order_ids])
            ->andWhere('oc.merc_uuid is not null')
            ->all();

        $query = ArrayHelper::index($query, 'uuid');
        $models = ArrayHelper::merge($models, $query);
        ArrayHelper::multisort($models, 'date_doc', SORT_DESC);

        return $models;
    }
}