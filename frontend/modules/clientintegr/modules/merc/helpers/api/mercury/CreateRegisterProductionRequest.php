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
use common\models\vetis\VetisProductItem;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\models\expiryDate;
use frontend\modules\clientintegr\modules\merc\models\productionDate;
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
        $request->enterprise['guid'] = $enterprise;
        /*$firstDate = new \DateTime($this->step2['dateOfProduction']['first_date']);
        $secondDate = new \DateTime($this->step2['dateOfProduction']['second_date']);
        $firstDateExpire = new \DateTime($this->step2['expiryDate']['first_date']);
        $secondDateExpire = new \DateTime($this->step2['expiryDate']['second_date']);*/
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

        $product = VetisProductItem::findOne(['guid' => $this->step2['product_name']]);

        $productionDate = new productionDate();
        $productionDate->first_date = $this->step2['dateOfProduction']['first_date'];
        $productionDate->second_date = $this->step2['dateOfProduction']['second_date'];

        $expiryDate = new expiryDate();
        $expiryDate ->first_date = $this->step2['expiryDate']['first_date'];
        $expiryDate ->second_date = $this->step2['expiryDate']['second_date'];

        $array['productiveBatch'] = [
            'product' => [
                'guid' => $product->product_guid,
            ],
            'subProduct' => [
                'guid' => $product->subproduct_guid,
            ],
            'productItem' => [
                'guid' => $this->step2['product_name']
            ],
            'volume' => $this->step2['volume'],
            'unit' => [
                'uuid' => $this->step2['unit']
            ],
            'dateOfProduction' => json_encode($this->convertDate($productionDate)),
            /*[
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
            ],*/
            
            'expiryDate' => json_encode($this->convertDate($expiryDate)),
               /* [
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
                ],*/
            'batchID' => $this->step2['batchID'],
            'perishable' => 'perishable'
        ];
        $array['vetDocument']['authentication']['cargoExpertized'] = 'VSEFULL';
        $request->vetDocument = [
            'authentication' => [
                'cargoExpertized' => 'VSEFULL'
            ]
        ];
        $request->productionOperation = $array;
        return $request;
    }

    private function convertDate($date)
    {
        $time = strtotime($date->first_date);

        $res = new GoodsDate();
        $res->firstDate = new ComplexDate();
        $res->firstDate->year = date('Y', $time);
        $res->firstDate->month = date('m', $time);
        $res->firstDate->day = date('d', $time);
        $res->firstDate->hour = date('h', $time);

        if (isset($date->secondDate)) {
            $time = strtotime($date->second_date);

            $res->secondDate = new ComplexDate();
            $res->secondDate->year = date('Y', $time);
            $res->secondDate->month = date('m', $time);
            $res->secondDate->day = date('d', $time);
            $res->secondDate->hour = date('h', $time);
        }
        return $res;
    }

}