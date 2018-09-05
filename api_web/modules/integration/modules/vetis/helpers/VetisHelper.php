<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 8/30/2018
 * Time: 4:25 PM
 */

namespace api_web\modules\integration\modules\vetis\helpers;

use api\common\models\merc\MercVsd;

use yii\db\ActiveQuery;
use yii\db\Query;
use frontend\modules\clientintegr\modules\merc\helpers\api\ikar\ikarApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use yii\web\BadRequestHttpException;

/**
 * Класс для работы с ВСД
 * */
class VetisHelper
{
    /**@var array $expertizeList расшифровки статусов экспертиз */
    public static $expertizeList = [
        'UNKNOWN'     => 'Результат неизвестен',
        'UNDEFINED'   => 'Результат невозможно определить (не нормируется)',
        'POSITIVE'    => 'Положительный результат',
        'NEGATIVE'    => 'Отрицательный результат',
        'UNFULFILLED' => 'Не проводилось',
        'VSERAW'      => 'ВСЭ подвергнуто сырьё, из которого произведена продукция',
        'VSEFULL'     => 'Продукция подвергнута ВСЭ в полном объеме'
    ];

    /**
     * Получение краткой информации о ВСД
     * @param string $uuid
     * @throws BadRequestHttpException
     * */
    public function getShortInfoVsd($uuid)
    {
        $this->uuid = $uuid;
        $doc = mercuryApi::getInstance()->getVetDocumentByUUID($uuid);
        if (!$doc) {
            throw new BadRequestHttpException('Uuid is bad');
        }
        $vsdModel = MercVsd::findOne(['uuid' => $uuid]);
        $arProducerName = unserialize($vsdModel->producer_name);
        $this->producer_name = reset($arProducerName);
        $country_raw = ikarApi::getInstance(\Yii::$app->user->identity->organization_id)->getCountryByGuid($doc->certifiedConsignment->batch->origin->country->guid);
        $this->country_name = isset($country_raw) ? $country_raw->country->name : null;
        if (isset($doc->referencedDocument)) {
            $this->setTransportWaybill($doc->referencedDocument);
        }
        $this->cargo_expertized = isset($doc->authentication->cargoExpertized) ?
            self::$expertizeList[$doc->authentication->cargoExpertized] : null;
        $this->location_prosperity = $doc->authentication->locationProsperity;
        $this->specialMarks = isset($doc->authentication->specialMarks) ? $doc->authentication->specialMarks : null;
        $this->vehicle_number = $vsdModel->vehicle_number;

        return $this;
    }

    public function getQueryByUuid()
    {
        $enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
        return MercVsd::find()->where(['recipient_guid' => $enterpriseGuid]);
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
     * @return Query
     * */
    public function getOrdersQueryVetis()
    {
        $tableName = $this->getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
        $query = (new Query())
            ->select(
                [
                    'COALESCE(o.id, \'order_not_installed\' ) as group_name',
                    'COUNT(m.id) as count',
                    'o.created_at',
                    'o.total_price',
                    'GROUP_CONCAT(`wc`.`merc_uuid` SEPARATOR \',\') AS `uuids`'
                ]
            )
            ->from('`' . $tableName . '`.merc_vsd m')
            ->leftJoin('`' . $tableName . '`.waybill_content wc', 'wc.merc_uuid = m.uuid COLLATE utf8_unicode_ci')
            ->leftJoin('`' . $tableName . '`.waybill w', 'w.id = wc.waybill_id')
            ->leftJoin('order_content oc', 'oc.id = wc.order_content_id')
            ->leftJoin('order o', 'o.id = oc.order_id')
            ->where('w.service_id = 4')
            ->groupBy('group_name')
            ->orderBy(['group_name' => SORT_DESC]);

        return $query;
    }

    private function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }
}