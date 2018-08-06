<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.2018
 * Time: 13:29
 */

namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;


use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercStockEntry;
use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use yii\base\Component;

class CreateRegisterProductionRequest extends Component{

    public $localTransactionId;
    public $initiator;
    public $step1;
    public $step2;


    public function getRegisterProductionRequest()
    {
        $request = new RegisterProductionOperationRequest();
        $request->localTransactionId = $this->localTransactionId;
        $request->initiator = $this->initiator;
        $enterprise = mercDicconst::getSetting('enterprise_guid');
        $request->enterprise['uuid'] = $enterprise;
        $firstDate = new \DateTime($this->step2['dateOfProduction']['first_date']);
        $secondDate = new \DateTime($this->step2['dateOfProduction']['second_date']);
        $firstDateExpire = new \DateTime($this->step2['expiryDate']['first_date']);
        $secondDateExpire = new \DateTime($this->step2['expiryDate']['second_date']);
        $array = [];

        foreach ($this->step1 as $id => $value){
            $stockEntry = MercStockEntry::findOne(['id' => $id]);
            $rawData = unserialize($stockEntry->raw_data);
            if($stockEntry){
                $array['rawBatch'] = [
                    'sourceStockEntry' => [
                        'guid' => $stockEntry->guid
                    ],
                    'volume' => $value['select_amount'],
                    'unit' => [
                        'uuid' => $rawData->batch->unit->uuid
                    ],
                ];
            }
        }


        $array['rawBatch'] = [
            'sourceStockEntry' => [
                'uuid' => $this->step2['product']
            ],
            'volume' => $this->step2['volume'],
            'unit' => [
                'uuid' => $this->step2['unit']
            ],
        ];

        $arr = explode('|', $this->step2['product_name']);
        if(isset($arr[1])){
            $productUUID = trim($arr[1]);
        }else{
            $productUUID = $this->step2['product_name'];
        }

        $array['productiveBatch'] = [
            'product' => [
                'uuid' => $this->step2['product']
            ],
            'subProduct' => [
                'uuid' => $this->step2['subProduct']
            ],
            'productItem' => [
                'uuid' => $productUUID
            ],
            'volume' => $this->step2['volume'],
            'unit' => [
                'uuid' => $this->step2['unit']
            ],
            'dateOfProduction' =>
            [
                'firstDate' => [
                    'year' => $firstDate->format('Y'),
                    'month' => $firstDate->format('m'),
                    'day' => $firstDate->format('d'),
                    'hour' => $firstDate->format('h')
                ],
                'secondDate' => [
                    'year' => $secondDate->format('Y'),
                    'month' => $secondDate->format('m'),
                    'day' => $secondDate->format('d'),
                    'hour' => $secondDate->format('h')
                ]
            ],
            'expiryDate' =>
                [
                    'firstDate' => [
                        'year' => $firstDateExpire->format('Y'),
                        'month' => $firstDateExpire->format('m'),
                        'day' => $firstDateExpire->format('d'),
                        'hour' => $firstDateExpire->format('h')
                    ],
                    'secondDate' => [
                        'year' => $secondDateExpire->format('Y'),
                        'month' => $secondDateExpire->format('m'),
                        'day' => $secondDateExpire->format('d'),
                        'hour' => $secondDateExpire->format('h')
                    ]
                ],
            'batchID' => $this->step2['batchID'],
            'perishable' => 'perishable'
        ];
        $request->productionOperation = $array;
        return $request;
    }

}