<?php

namespace frontend\modules\clientintegr\modules\merc\helpers;

use yii\base\Component;
use yii\data\ArrayDataProvider;

class vetDocumentsList extends Component
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

    const DOC_STATUS_ALL = 'ALL';
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
        self::DOC_STATUS_CONFIRMED => 'new',
        self::DOC_STATUS_WITHDRAWN => 'cancelled',
        self::DOC_STATUS_UTILIZED => 'done',
    ];

    public function createDocumentsList($list) {

        $result = [];
        foreach ($list as $item)
        {
            $unit = mercApi::getInstance()->getUnitByGuid($item->ns2batch->ns2unit->bsguid);

            $recipient = mercApi::getInstance()->getBusinessEntityByUuid($item->ns2consignor->entbusinessEntity->bsuuid->__toString());
            $result[] = [
                'UUID' => $item->bsuuid,
                'number' => '',
                'date_doc' => $item->ns2issueDate,
                'status' => '<span class="status ' . $this->status_color[$item->ns2status->__toString()] . '">'.self::$statuses[$item->ns2status->__toString()].'</span>',
                'status_raw' => $item->ns2status->__toString(),
                'product_name' => $item->ns2batch->ns2productItem->prodname,
                'amount' => $item->ns2batch->ns2volume." ".$unit->soapBody->wsgetUnitByGuidResponse->comunit->comname->__toString(),
                'production_date' => $this->getDate($item->ns2batch->ns2dateOfProduction),
                'recipient_name' => $recipient->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity->dtname->__toString(),
            ];
        }

        return $result;
    }

    public function getArrayDataProvider()
    {
        //$pageSize = isset($params['per-page']) ? intval($params['per-page']) : Yii::$app->params['defaultPageSize'];

        $api = mercApi::getInstance();

        $result = $api->getVetDocumentList();

        if(empty($result))
            $data = [];
        else
            $data = $this->createDocumentsList($result->envBody->receiveApplicationResultResponse->application->result->ns1getVetDocumentListResponse->ns2vetDocumentList->ns2vetDocument);

       /* var_dump($result->envBody->receiveApplicationResultResponse->application->result->ns1getVetDocumentListResponse->ns2vetDocumentList->ns2vetDocument);
        die();*/

       $sort = [
           'attributes' => [
               'UUID',
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
            'key' => 'UUID',
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