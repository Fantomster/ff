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
        $request->enterprise = $enterprise;
        //dd($this);
        $array = [];
        $array['rawBatch'] = [
            'sourceStockEntry' => [
                'uuid' => $this->step2['product']
            ],
            'volume' => $this->step2['volume'],
            'unit' => [
                'uuid' => $this->step2['unit']
            ],
        ];

        $array['productiveBatch'] = [
            'product' => [
                'uuid' => $this->step2['product']
            ],
            'subProduct' => [
                'uuid' => $this->step2['subProduct']
            ],
            'volume' => $this->step2['volume'],
            'unit' => [
                'uuid' => $this->step2['unit']
            ],
        ];
        $request->productionOperation = $array;

        //dd($request);

        $consigments = [];
        $vetCertificates = [];
        foreach ($this->step1 as $id => $product) {
//            $consigment = new Consignment();
//            $consigment->id = 'con'.$id;
//            $stock = MercStockEntry::findOne(['id' => $id]);
//            $stock_raw = unserialize($stock->raw_data);
//            if($stock->product_name != $product['product_name'])
//            {
//
//            }
//            $consigment->volume = $product['select_amount'];
//            $consigment->unit = new Unit();
//            $consigment->unit = $stock_raw->batch->unit;
//
//            $consigment->sourceStockEntry = new StockEntry();
//            $consigment->sourceStockEntry->uuid = $stock->uuid;
//            $consigment->sourceStockEntry->guid = $stock->guid;
//
//            $consigments[] = $consigment;
//
//            $vetCertificate = new VetDocument();
//            $vetCertificate->for = 'con'.$id;
//            $vetCertificate->authentication = new VeterinaryAuthentication();
//            $vetCertificate->authentication->purpose = new Purpose();
//
//            $vetCertificates[] = $vetCertificate;
        }
        //dd($request);
        return $request;
    }

}