<?php

namespace api_web\classes;

use api\common\models\AllMaps;
use api_web\components\WebApi;
use api_web\modules\integration\interfaces\ServiceInterface;
use api_web\modules\integration\modules\one_s\models\one_sService;
use api_web\modules\integration\modules\rkeeper\models\rkeeperService;
use api_web\modules\integration\modules\iiko\models\iikoService;
use common\models\Order;
use common\models\OrderContent;
use common\models\OuterAgent;
use common\models\OuterProduct;
use common\models\OuterStore;
use common\models\OuterUnit;
use common\models\Waybill;
use common\models\WaybillContent;
use yii\base\Exception;

class IntegrationWebApi extends WebApi
{

    private static $service = [
        iikoService::class,
        rkeeperService::class,
        one_sService::class
    ];

    /**
     * Список интеграторов и лицензий
     * @return array
     * @throws Exception
     */
    public function list()
    {
        $result = [];
        foreach (self::$service as $service_class) {
            /**
             * @var $service ServiceInterface
             */
            $service = new $service_class();

            if (!($service instanceof ServiceInterface)) {
                throw new Exception(get_class($service) . ' not implements ServiceInterface');
            }

            $license = $this->prepareLicense($service->getLicenseMixCart());
            $license['status'] = $service->getLicenseMixCartActive() === true ? 'Активна' : "Не активна";

            $result[] = [
                'service' => $service->getServiceName(),
                'image' => \Yii::$app->params['web'] . 'images/' . $service->getServiceName() . '.jpg',
                'license' => $license,
                'options' => $service->getOptions()
            ];


        }
        return ['services' => $result];
    }

    /**
     * Лицензии к выдаче
     * @param $model
     * @return array
     */
    private function prepareLicense($model)
    {
        if (!empty($model)) {
            return [
                "from" => date('d.m.Y', strtotime($model->fd)),
                "to" => date('d.m.Y', strtotime($model->td)),
                "number" => $model->id
            ];
        }
        return [
            "from" => null,
            "to" => null,
            "number" => null
        ];
    }


    /**
     * integration: Создание накладной к заказу
     * @param array $post
     * @return array
     */
    public function handleWaybill(array $post): array
    {
        if (!isset($post)) {
            throw new BadRequestHttpException("empty_param|post");
        }

        if (!isset($post['service_id'])) {
            throw new BadRequestHttpException("empty_param|service_id");
        }

        $organizationID = $this->user->organization_id;
        $ediNumber = '';
        $outerAgentUUID = '';
        $outerStoreUUID = '';
        $acquirerID = 0;

        if (isset($post['order_id'])) {
            $order = Order::findOne(['id' => $post['order_id']]);

            if (!$order) {
                throw new BadRequestHttpException("order_not_found");
            }
            $outerAgent = OuterAgent::findOne(['vendor_id' => $order->vendor_id]);
            if ($outerAgent) {
                $outerAgentUUID = $outerAgent->outer_uid;
            }
            $outerStore = OuterStore::findOne(['org_id' => $organizationID]);
            if ($outerStore) {
                $outerStoreUUID = $outerStore->outer_uid;
            }

            $orderContent = OrderContent::findOne(['order_id' => $order->id]);
            if($orderContent->edi_number){
                $arr = explode('-', $orderContent->edi_number);
                if(isset($arr[1])){
                    $i = (int)$arr[1];
                    $ediNumber = $arr[0] . "-" . $i;
                }else{
                    $ediNumber = $orderContent->edi_number . "-1";
                }
            }else{
                $waybillsCount = Waybill::find()->where(['order_id' => $post['order_id']])->count();
                if(!$waybillsCount){
                    $waybillsCount = 1;
                }
                $ediNumber = $post['order_id'] . "-" . $waybillsCount;
            }
        }

        $waybill = new Waybill();
        $waybill->service_id = (int)$post['service_id'] ?? null;
        $waybill->outer_number_code = $ediNumber;
        $waybill->outer_contractor_uuid = $outerAgentUUID;
        $waybill->outer_store_uuid = $outerStoreUUID;
        $waybill->acquirer_id = $acquirerID;
        $waybill->save();

        return ['success' => true, 'waybill_id' => $waybill->id];
    }


    /**
     * integration: Сброс данных позиции, на значения из заказа
     * @param array $post
     * @return array
     */
    public function resetWaybillContent(array $post): array
    {
        if (!isset($post['waybill_content_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_content_id");
        }

        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if(!$waybillContent){
            throw new BadRequestHttpException("not found");
        }

        $orderContent = OrderContent::findOne(['id' => $waybillContent->order_content_id]);
        if($orderContent){
            $waybillContent->quantity_waybill = $orderContent->quantity;
            $waybillContent->price_without_vat = (int)$orderContent->price;
            $waybillContent->vat_waybill = $orderContent->vat_product;
            $waybillContent->price_with_vat = (int)($orderContent->price + ($orderContent->price * $orderContent->vat_product));
            $waybillContent->sum_without_vat = (int)$orderContent->price * $orderContent->quantity;
            $waybillContent->sum_with_vat = $waybillContent->price_with_vat * $orderContent->quantity;
            $allMap = AllMaps::findOne(['product_id' => $orderContent->product_id]);
            if($allMap){
                $waybillContent->product_outer_id = $allMap->serviceproduct_id;
            }
        }else{
            throw new BadRequestHttpException("order content not found");
        }

        $waybillContent->save();

        return ['success' => true];
    }


    /**
     * integration: Позиция накладной - Детальная информация
     * @param array $post
     * @return array
     */
    public function showWaybillContent(array $post): array
    {
        if (!isset($post['waybill_content_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_content_id");
        }

        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if(!$waybillContent){
            throw new BadRequestHttpException("not found");
        }
        $arr = $waybillContent->attributes;

        $orderContent = OrderContent::findOne(['id' => $waybillContent->order_content_id]);
        if($orderContent){
            $allMap = AllMaps::findOne(['product_id' => $orderContent->product_id]);
            if($allMap){
                $arr['koef'] = $allMap->koef;
                $arr['serviceproduct_id'] = $allMap->serviceproduct_id;
                $arr['store_rid'] = $allMap->store_rid;
                $outerProduct = OuterProduct::findOne(['id' => $allMap->serviceproduct_id]);
                if($outerProduct){
                    $arr['outer_product_name'] = $outerProduct->name;
                    $arr['outer_product_id'] = $outerProduct->id;
                    $arr['product_id_equality'] = true;
                }else{
                    $arr['product_id_equality'] = false;
                }
                $outerStore = OuterStore::findOne(['outer_uid' => $allMap->store_rid]);
                if($outerStore){
                    $arr['outer_store_name'] = $outerStore->name;
                    $arr['outer_store_id'] = $outerStore->id;
                    $arr['store_id_equality'] = true;
                }else{
                    $arr['store_id_equality'] = false;
                }
                $outerUnit = OuterUnit::findOne(['outer_uid' => $allMap->unit_rid]);
                if($outerUnit){
                    $arr['outer_unit_name'] = $outerUnit->name;
                    $arr['outer_unit_id'] = $outerUnit->id;
                }
            }
        }

        return $arr;
    }

}