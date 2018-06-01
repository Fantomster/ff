<?php

namespace frontend\modules\clientintegr\modules\merc\helpers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class vetDocumentsChangeList extends Model
{
    const DOC_TYPE_INCOMMING = 'INCOMING';
    const DOC_TYPE_OUTGOING = 'OUTGOING';
    const DOC_TYPE_PRODUCTIVE = 'PRODUCTIVE';
    const DOC_TYPE_RETURNABLE = 'RETURNABLE';
    const DOC_TYPE_TRANSPORT = 'TRANSPORT';

    public $types = [
        self::DOC_TYPE_INCOMMING => 'Входящий ВСД',
        self::DOC_TYPE_OUTGOING => 'Исходящий ВСД',
        self::DOC_TYPE_PRODUCTIVE => 'Производственный ВСД',
        self::DOC_TYPE_RETURNABLE => 'Возвратный ВСД',
        self::DOC_TYPE_TRANSPORT => 'Транспортный ВСД',
    ];

    const DOC_STATUS_ALL = null;
    const DOC_STATUS_CONFIRMED = 'CONFIRMED';
    const DOC_STATUS_WITHDRAWN = 'WITHDRAWN';
    const DOC_STATUS_UTILIZED = 'UTILIZED';

    public static $statuses = [
        self::DOC_STATUS_ALL => 'Все',
        self::DOC_STATUS_CONFIRMED => 'Оформлен',
        self::DOC_STATUS_WITHDRAWN => 'Аннулирован',
        self::DOC_STATUS_UTILIZED => 'Погашен',
    ];

    public $status_color = [
        self::DOC_STATUS_CONFIRMED => '',
        self::DOC_STATUS_WITHDRAWN => 'cancelled',
        self::DOC_STATUS_UTILIZED => 'done',
    ];

    public $recipient;

    public function rules()
    {
        return [
            [['recipient'], 'safe'],
        ];
    }

    public function updateDocumentsList($list) {
        $cache = \Yii::$app->cache;
        $guid = mercDicconst::getSetting('enterprise_guid');
        foreach ($list as $item)
        {
            if(!$cache->get('vetDocRaw_'.$item->bsuuid->__toString()))
                $cache->add('vetDocRaw_'.$item->bsuuid->__toString(), $item->asXML(),60);

            $unit = mercApi::getInstance()->getUnitByGuid($item->ns2batch->ns2unit->bsguid);
            $recipient = mercApi::getInstance()->getBusinessEntityByUuid($item->ns2consignor->entbusinessEntity->bsuuid->__toString());

            $model = MercVsd::findOne(['uuid' => $item->bsuuid->__toString()]);

            if($model == null)
                $model = new MercVsd();

            $model->setAttributes([
                'uuid' => $item->bsuuid->__toString(),
                'number' => $this->getNumber($item->ns2issueSeries, $item->ns2issueNumber),
                'date_doc' => $item->ns2issueDate->__toString(),
                'status' => $item->ns2status->__toString(),
                'type' => $item->ns2type->__toString(),
                'product_name' => $item->ns2batch->ns2productItem->prodname->__toString(),
                'amount' => $item->ns2batch->ns2volume->__toString(),
                'unit' => $unit->soapBody->wsgetUnitByGuidResponse->comunit->comname->__toString(),
                'production_date' => $this->getDate($item->ns2batch->ns2dateOfProduction),
                'recipient_name' => $recipient->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity->dtname->__toString(),
                'guid' => $guid,
                'consignor' => $item->ns2consignor->ententerprise->bsguid->__toString(),
            ]);

            if(!$model->save()) {
                throw new \Exception('VSD save error');
            }

        }
    }

    public function getNumber($series, $number)
    {
        if(empty($number) && empty($series))
            return null;

        $res = '';
        if(isset($series))
            $res =  $series.' ';

        if(isset($number))
            $res .=  $number;

        return $res;
    }

    public function updateData($last_visit)
    {
        $api = mercApi::getInstance();

        $result = $api->getVetDocumentChangeList($last_visit);

        if(!empty($result))
            $this->updateDocumentsList($result->envBody->receiveApplicationResultResponse->application->result->ns1getVetDocumentChangesListResponse->ns2vetDocumentList->ns2vetDocument);
    }

    public function getDate($date_raw)
    {
        $first_date =  $date_raw->ns2firstDate->bsyear.'-'.$date_raw->ns2firstDate->bsmonth.'-'.$date_raw->ns2firstDate->bsday;
        $first_date .= (isset($date_raw->ns2firstDate->hour)) ? ' '.$date_raw->ns2firstDate->hour.":00:00" : "";

        if($date_raw->ns2secondDate)
        {
            $second_date = $date_raw->ns2secondDate->bsyear.'-'.$date_raw->ns2secondDate->bsmonth.'-'.$date_raw->ns2secondDate->bsday.' '.$date_raw->ns2secondDate->hour.":00:00";
            $second_date .= (isset($date_raw->ns2secondDate->hour)) ? ' '.$date_raw->ns2secondDate->hour.":00:00" : "";
            return 'с '.$first_date.' до '.$second_date;
        }

        return $first_date;
    }

}