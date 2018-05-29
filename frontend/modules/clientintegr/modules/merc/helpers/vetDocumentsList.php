<?php

namespace frontend\modules\clientintegr\modules\merc\helpers;

use yii\base\Model;
use yii\data\ArrayDataProvider;

class vetDocumentsList extends Model
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

    public $recipentList = [null => 'Все'];
    public $status;
    public $date_from;
    public $date_to;
    public $recipient;

    private $_params;

    public function rules()
    {
        return [
            [['recipient', 'date_from', 'date_to','status'], 'safe'],
        ];
    }

    public function createDocumentsList($list) {
        $cache = \Yii::$app->cache;
        $result = [];
        foreach ($list as $item)
        {
            if(!$cache->get('vetDocRaw_'.$item->bsuuid->__toString()))
                $cache->add('vetDocRaw_'.$item->bsuuid->__toString(), $item->asXML(),60);

            $unit = mercApi::getInstance()->getUnitByGuid($item->ns2batch->ns2unit->bsguid);
            $recipient = mercApi::getInstance()->getBusinessEntityByUuid($item->ns2consignor->entbusinessEntity->bsuuid->__toString());
            $result[] = [
                'uuid' => $item->bsuuid->__toString(),
                'number' => $this->getNumber($item->ns2issueSeries, $item->ns2issueNumber),
                'date_doc' => $item->ns2issueDate,
                'status' => '<span class="status ' . $this->status_color[$item->ns2status->__toString()] . '">'.self::$statuses[$item->ns2status->__toString()].'</span>',
                'status_raw' => $item->ns2status->__toString(),
                'product_name' => $item->ns2batch->ns2productItem->prodname,
                'amount' => $item->ns2batch->ns2volume." ".$unit->soapBody->wsgetUnitByGuidResponse->comunit->comname->__toString(),
                'production_date' => $this->getDate($item->ns2batch->ns2dateOfProduction),
                'recipient_name' => $recipient->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity->dtname->__toString(),
            ];

            $this->recipentList[$recipient->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity->dtname->__toString()] = $recipient->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity->dtname->__toString();
        }

        return $result;
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

    public function getArrayDataProvider()
    {
        $api = mercApi::getInstance();

        $result = $api->getVetDocumentList($this->status);

        if(empty($result))
            $data = [];
        else
            $data = $this->createDocumentsList($result->envBody->receiveApplicationResultResponse->application->result->ns1getVetDocumentListResponse->ns2vetDocumentList->ns2vetDocument);

        if(!empty($this->recipient)) {
            $data = array_filter($data, [$this, 'filterRecipient']);
        }

        if(!empty($this->date_from))
            $data = array_filter($data, [$this, 'filterDate']);

        $sort = [
           'attributes' => [
               'uuid',
               'number',
               'date_doc',
               'status',
               'product_name',
               'amount',
               'production_date',
               'recipient_name'
           ],
           'defaultOrder' => [
               'date_doc' => SORT_DESC
           ]
       ];


        $dataProvider = new ArrayDataProvider([
            'key' => 'uuid',
            'allModels' => $data,
             'sort' => $sort,
            /*'pagination' => [
                'class' => 'backend\modules\mailbox\components\Pagination',
                'pageSize' => 10, //($pageSize != -1) ? $pageSize : false,
                'offset' => 0
            ],*/
        ]);

        return $dataProvider;
    }

    private function filterDate($var)
    {
        $from = strtotime($this->date_from);
        $to = strtotime($this->date_to);
        $date = strtotime($var['date_doc']);

        return (($date >= $from) && ($date <= $to));
    }

    private function filterRecipient($var)
    {
        return ($var['recipient_name'] == $this->recipient);
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