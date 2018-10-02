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
        $outerAgentUUID = $post['outer_contractor_uuid'] ?? null;
        $outerStoreUUID = $post['outer_store_uuid'] ?? null;
        $acquirerID = (int)$post['acquirer_id'] ?? null;

        if (isset($post['order_id'])) {
            $order = Order::findOne(['id' => $post['order_id']]);

            if (empty($order)) {
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

            $orderContent = OrderContent::findAll(['order_id' => $order->id]);
            $arr = [];
            foreach ($orderContent as $item) {
                $arr[] = $item->edi_number;
            }
            $max = max($arr);
            if ($max) {
                $acquirerID += 1;
            }
        }

        $waybill = new Waybill();
        $waybill->acquirer_id = $acquirerID ?? null;
        $waybill->service_id = (int)$post['service_id'] ?? null;
        $waybill->bill_status_id = (int)$post['bill_status_id'] ?? null;
        $waybill->outer_number_code = $post['outer_number_code'] ?? null;
        $waybill->outer_number_additional = $post['outer_number_additional'] ?? null;
        $waybill->outer_store_uuid = $outerStoreUUID ?? null;
        $waybill->outer_contractor_uuid = $outerAgentUUID ?? null;
        $waybill->vat_included = (int)$post['vat_included'] ?? null;
        $waybill->outer_duedate = $post['outer_duedate'] ?? null;
        $waybill->outer_order_date = $post['outer_order_date'] ?? null;
        $waybill->doc_date = $post['doc_date'] ?? null;
        $waybill->outer_note = $post['outer_note'] ?? null;
        $waybill->save();
        foreach ($post['data'] as $data) {
            $waybillContent = new WaybillContent();
            $waybillContent->order_content_id = $data['order_content_id'] ?? null;
            $waybillContent->product_outer_id = $data['product_outer_id'] ?? null;
            $waybillContent->quantity_waybill = $data['quantity_waybill'] ?? null;
            $waybillContent->merc_uuid = $data['merc_uuid'] ?? null;
            $waybillContent->price_with_vat = (int)$data['price_with_vat'] ?? null;
            $waybillContent->price_without_vat = (int)$data['price_without_vat'] ?? null;
            $waybillContent->sum_without_vat = (int)$data['sum_without_vat'] ?? null;
            $waybillContent->sum_with_vat = (int)$data['sum_with_vat'] ?? null;
            $waybillContent->vat_waybill = $data['vat_waybill'] ?? null;
            $waybillContent->waybill_id = $waybill->id;
            $waybillContent->save();
        }

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
        }

        $waybillContent->save();

        return ['success' => true, 'waybill_content_id' => $post['waybill_content_id']];
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