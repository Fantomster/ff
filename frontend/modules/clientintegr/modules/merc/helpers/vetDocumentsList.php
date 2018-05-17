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
                'type' => $this->types[$item->ns2type->__toString()],
                'product_name' => $item->ns2batch->ns2productItem->prodname,
                'amount' => $item->ns2batch->ns2volume." ".$unit->soapBody->wsgetUnitByGuidResponse->comunit->comname->__toString(),
                'production_date' => $this->getData($item->ns2batch->ns2dateOfProduction->ns2firstDate),
                'recipient_name' => $recipient->soapenvBody->v2getBusinessEntityByUuidResponse->dtbusinessEntity->dtname->__toString(),
            ];
        }

        return $result;
    }

    private function getData($raw_data)
    {
        return $raw_data->bsyear.'-'.$raw_data->bsmonth.'-'.$raw_data->bsday.' '.$raw_data->hour.":00:00";
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

        $dataProvider = new ArrayDataProvider([
            //'key' => 'uid',
            'allModels' => $data,
            // 'sort' => $sort,
            /*'pagination' => [
                'class' => 'backend\modules\mailbox\components\Pagination',
                'pageSize' => 10, //($pageSize != -1) ? $pageSize : false,
                'offset' => 0
            ],*/
        ]);

        //$dataProvider->setSort(['defaultOrder' => ['UUID'=>SORT_DESC], 'attributes' => ['UUID', 'number', 'date_doc', 'type', 'product_name', 'aount', 'production_date', 'recipient_name']]);

        return $dataProvider;
    }

}