<?php

namespace frontend\modules\clientintegr\modules\merc\helpers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocument;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentByUUIDRequest;
use yii\base\Model;

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
            if($item->vetDType == getVetDocumentByUUIDRequest::DOC_TYPE_PRODUCTIVE)
                continue;

            if(!$cache->get('vetDocRaw_'.$item->uuid))
                $cache->add('vetDocRaw_'.$item->uuid, $item,60);

            $unit = dictsApi::getInstance()->getUnitByGuid($item->certifiedConsignment->batch->unit->guid);
            $recipient = cerberApi::getInstance()->getEnterpriseByUuid($item->certifiedConsignment->consignor->enterprise->uuid);
            $recipient = $recipient->enterprise;
            $model = MercVsd::findOne(['uuid' => $item->uuid, 'guid' => $guid]);

            if($model == null)
                $model = new MercVsd();

            $model->setAttributes([
                'uuid' => $item->uuid,
                'number' => $this->getNumber($item->issueSeries, $item->issueNumber),
                'date_doc' => $item->issueDate,
                'status' => $item->vetDStatus,
                'type' => $item->vetDType,
                'product_name' => $item->certifiedConsignment->batch->productItem->name,
                'amount' => $item->certifiedConsignment->batch->volume,
                'unit' => $unit->unit->name,
                'production_date' => $this->getDate($item->certifiedConsignment->batch->dateOfProduction),
                'recipient_name' =>  $recipient->name.'('.
                    $recipient->address->addressView
                    .')',
                'guid' => $guid,
                'consignor' => $item->certifiedConsignment->consignor->enterprise->guid,
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
        $api = mercuryApi::getInstance();

        $result = $api->getVetDocumentChangeList($last_visit);

        if(isset($result->application->result->any['getVetDocumentChangesListResponse']->vetDocumentList->vetDocument))
            $this->updateDocumentsList($result->application->result->any['getVetDocumentChangesListResponse']->vetDocumentList->vetDocument);
    }

    public function getDate($date_raw)
    {
        $first_date =  $date_raw->firstDate->year.'-'.$date_raw->firstDate->month.'-'.$date_raw->firstDate->day;
        $first_date .= (isset($date_raw->firstDate->hour)) ? ' '.$date_raw->firstDate->hour.":00:00" : "";

        if($date_raw->secondDate)
        {
            $second_date = $date_raw->secondDate->year.'-'.$date_raw->secondDate->month.'-'.$date_raw->secondDate->day.' '.$date_raw->secondDate->hour.":00:00";
            $second_date .= (isset($date_raw->secondDate->hour)) ? ' '.$date_raw->secondDate->hour.":00:00" : "";
            return 'с '.$first_date.' до '.$second_date;
        }

        return $first_date;
    }

}