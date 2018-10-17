<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.2018
 * Time: 13:29
 */

namespace api_web\modules\integration\modules\vetis\api\mercury;

use api\common\models\merc\MercStockEntry;
use api_web\components\Registry;
use common\models\IntegrationSettingValue;
use common\models\vetis\VetisProductItem;
use yii\base\Component;

/**
 * Class CreateRegisterProductionRequest
 *
 * @package api_web\modules\integration\modules\vetis\api\mercury
 */
class CreateRegisterProductionRequest extends Component
{

    /**
     * @var
     */
    public $localTransactionId;
    /**
     * @var
     */
    public $initiator;
    /**
     * @var
     */
    public $step1;
    /**
     * @var
     */
    public $step2;

    /**
     * @return RegisterProductionOperationRequest
     * @throws \Exception
     */
    public function getRegisterProductionRequest()
    {
        $request = new RegisterProductionOperationRequest();
        $request->localTransactionId = $this->localTransactionId;
        $request->initiator = $this->initiator;
        /**@var string Че реально это будет работать с консоли без orgID ??? */
        $enterprise = IntegrationSettingValue::getSettingsByServiceId(Registry::MERC_SERVICE_ID,
            null, ['enterprise_guid']);
        $request->enterprise['guid'] = $enterprise;
        $array = [];

        foreach ($this->step1 as $id => $value) {
            $stockEntry = MercStockEntry::findOne(['id' => $id]);
            if ($stockEntry) {
                $rawData = unserialize($stockEntry->raw_data);
                $array['rawBatch'][] = [
                    'sourceStockEntry' => [
                        'guid' => $stockEntry->guid
                    ],
                    'volume'           => $value['select_amount'],
                    'unit'             => [
                        'uuid' => $rawData->batch->unit->uuid
                    ],
                ];
            }
        }

        $product = VetisProductItem::findOne(['guid' => $this->step2['product_name']]);

        $productionDate = $this->step2['dateOfProduction'];

        $expiryDate = $this->step2['expiryDate'];

        $array['productiveBatch'] = [
            'product'          => [
                'guid' => $product->product_guid,
            ],
            'subProduct'       => [
                'guid' => $product->subproduct_guid,
            ],
            'productItem'      => [
                'guid' => $this->step2['product_name']
            ],
            'volume'           => $this->step2['volume'],
            'unit'             => [
                'guid' => $this->step2['unit']
            ],
            'dateOfProduction' => json_decode(json_encode($this->convertDate($productionDate)), true),

            'expiryDate' => json_decode(json_encode($this->convertDate($expiryDate)), true),

            'batchID'    => $this->step2['batchID'],
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

    /**
     * @param $date
     * @return GoodsDate
     */
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