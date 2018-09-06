<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 8/30/2018
 * Time: 4:25 PM
 */

namespace api_web\modules\integration\modules\vetis\helpers;

use api\common\models\merc\MercVsd;
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
}