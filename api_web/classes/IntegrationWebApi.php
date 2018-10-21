<?php

namespace api_web\classes;

use api\common\models\AllMaps;
use api_web\components\Registry;
use api_web\components\WebApi;
use common\models\licenses\License;
use common\models\Order;
use common\models\OrderContent;
use common\models\OuterAgent;
use common\models\OuterProduct;
use common\models\OuterStore;
use common\models\OuterUnit;
use common\models\Waybill;
use common\models\WaybillContent;
use yii\web\BadRequestHttpException;

/**
 * Class IntegrationWebApi
 *
 * @package api_web\classes
 */
class IntegrationWebApi extends WebApi
{
    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException|\Exception
     */
    public function userServiceSet($request)
    {
        $this->validateRequest($request, ['service_id']);
        $license = License::checkByServiceId($this->user->id, $request['service_id']);
        if ($license) {
            $this->user->integration_service_id = $request['service_id'];
            $this->user->save();
            return ['result' => true];
        } else {
            throw new BadRequestHttpException('Dont have license for this service');
        }
    }

    /**
     * Список интеграторов и лицензий
     *
     * @return array
     * @throws \Exception
     */
    public function list()
    {
        $result = array_values(License::getAllLicense($this->user->organization_id, Registry::$integration_services));
        return [
            'services' => $result
        ];
    }

    /**
     * integration: Создание накладной к заказу
     *
     * @param array $post
     * @throws \Exception
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
            $order = Order::findOne(['id' => (int)$post['order_id'], 'client_id' => $this->user->organization_id]);

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
            if ($orderContent->edi_number) {
                $arr = explode('-', $orderContent->edi_number);
                if (isset($arr[1])) {
                    $i = (int)$arr[1];
                    $ediNumber = $arr[0] . "-" . $i;
                } else {
                    $ediNumber = $orderContent->edi_number . "-1";
                }
            } else {
                $waybillsCount = count($order->getWaybills($post['service_id']));
                if ($waybillsCount == 0) {
                    $waybillsCount = 1;
                }
                $ediNumber = $post['order_id'] . "-" . $waybillsCount;
            }
        }

        $waybill = new Waybill();
        $waybill->service_id = (int)$post['service_id'];
        $waybill->outer_number_code = $ediNumber;
        $waybill->outer_contractor_uuid = $outerAgentUUID;
        $waybill->outer_store_uuid = $outerStoreUUID;
        $waybill->acquirer_id = $acquirerID;
        $waybill->save();

        return ['success' => true, 'waybill_id' => $waybill->id];
    }

    /**
     * integration: Сброс данных позиции, на значения из заказа
     *
     * @param array $post
     * @throws \Exception
     * @return array
     */
    public function resetWaybillContent(array $post): array
    {
        if (!isset($post['waybill_content_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_content_id");
        }

        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill content not found");
        }

        $orderContent = OrderContent::findOne(['id' => $waybillContent->order_content_id]);
        if ($orderContent) {
            $waybillContent->quantity_waybill = $orderContent->quantity;
            $waybillContent->price_without_vat = (int)$orderContent->price;
            $waybillContent->vat_waybill = $orderContent->vat_product;
            $waybillContent->price_with_vat = (int)($orderContent->price + ($orderContent->price * $orderContent->vat_product));
            $waybillContent->sum_without_vat = (int)$orderContent->price * $orderContent->quantity;
            $waybillContent->sum_with_vat = $waybillContent->price_with_vat * $orderContent->quantity;
            $allMap = AllMaps::findOne(['product_id' => $orderContent->product_id]);
            if ($allMap) {
                $waybillContent->outer_product_id = $allMap->serviceproduct_id;
            }
        } else {
            throw new BadRequestHttpException("order content not found");
        }

        $waybillContent->save();

        return ['success' => true];
    }

    /**
     * integration: Позиция накладной - Детальная информация
     *
     * @param array $post
     * @throws \Exception
     * @return array
     */
    public function showWaybillContent(array $post): array
    {
        if (!isset($post['waybill_content_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_content_id");
        }

        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill content not found");
        }
        $arr = $waybillContent->attributes;

        $orderContent = OrderContent::findOne(['id' => $waybillContent->order_content_id]);
        if ($orderContent) {
            $allMap = AllMaps::findOne(['product_id' => $orderContent->product_id]);
            if ($allMap) {
                $arr['koef'] = $allMap->koef;
                $arr['serviceproduct_id'] = $allMap->serviceproduct_id;
                $arr['store_rid'] = $allMap->store_rid;
                $outerProduct = OuterProduct::findOne(['id' => $allMap->serviceproduct_id]);
                if ($outerProduct) {
                    $arr['outer_product_name'] = $outerProduct->name;
                    $arr['outer_product_id'] = $outerProduct->id;
                    $arr['product_id_equality'] = true;
                } else {
                    $arr['product_id_equality'] = false;
                }
                $outerStore = OuterStore::findOne(['outer_uid' => $allMap->store_rid]);
                if ($outerStore) {
                    $arr['outer_store_name'] = $outerStore->name;
                    $arr['outer_store_id'] = $outerStore->id;
                    $arr['store_id_equality'] = true;
                } else {
                    $arr['store_id_equality'] = false;
                }
                $outerUnit = OuterUnit::findOne(['outer_uid' => $allMap->unit_rid]);
                if ($outerUnit) {
                    $arr['outer_unit_name'] = $outerUnit->name;
                    $arr['outer_unit_id'] = $outerUnit->id;
                }
            }
        }

        return $arr;
    }

    /**
     * integration: Накладные - Обновление детальной информации позиции накладной
     *
     * @param array $post
     * @throws \Exception
     * @return array
     */
    public function updateWaybillContent(array $post): array
    {
        if (!isset($post['waybill_content_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_content_id");
        }
        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill content not found");
        }
        if (isset($post['vat_waybill'])) {
            $waybillContent->vat_waybill = (float)$post['vat_waybill'];
        }
        if (isset($post['outer_unit_id'])) {
            $waybillContent->outer_unit_id = (float)$post['outer_unit_id'];
        }
        $koef = null;
        $quan = null;
        if (isset($post['koef'])) {
            $koef = (float)$post['koef'];
        }
        if (isset($post['quantity_waybill'])) {
            $quan = (int)$post['quantity_waybill'];
        }

        return $this->handleWaybillContent($waybillContent, $post, $quan, $koef);
    }

    /**
     * @param WaybillContent $waybillContent
     * @param                $post
     * @param                $quan
     * @param                $koef
     * @return array
     */
    private function handleWaybillContent($waybillContent, $post, $quan, $koef)
    {
        if (isset($post['outer_product_id'])) {
            $waybillContent->outer_product_id = $post['outer_product_id'];
            //TODO refactor
            // поиск должен осуществляться по орг_ид
            $allMap = AllMaps::findOne(['product_id' => $post['outer_product_id']]);
            if ($allMap) {
                $outerStore = OuterStore::findOne(['id' => $allMap->store_rid]);
                if ($outerStore) {
                    $waybill = Waybill::findOne(['id' => $waybillContent->waybill_id]);
                    if ($waybill) {
                        $waybill->outer_store_uuid = $outerStore->outer_uid;
                        $waybill->save();
                    }
                }
            }
        }
        $orderContent = OrderContent::findOne(['id' => $waybillContent->order_content_id]);
        if (!$orderContent) {
            if (isset($post['price_without_vat'])) {
                $waybillContent->price_without_vat = (int)$post['price_without_vat'];
                if (isset($post['vat_waybill'])) {
                    $waybillContent->price_with_vat = (int)($post['price_without_vat'] + ($post['price_without_vat'] * $post['vat_waybill']));
                    if (isset($post['quantity_waybill'])) {
                        $waybillContent->sum_without_vat = (int)$post['price_without_vat'] * $post['quantity_waybill'];
                        $waybillContent->sum_with_vat = $waybillContent->price_with_vat * $post['quantity_waybill'];
                    }
                }
            }
        } else {
            if (isset($post['quantity_waybill']) && !isset($post['koef'])) {
                $koef = $post['quantity_waybill'] / $orderContent->quantity;
            }
            if (isset($post['koef']) && !isset($post['quantity_waybill'])) {
                $quan = $orderContent->quantity * $post['koef'];
            }
        }
        $waybillContent->quantity_waybill = $quan;
        $waybillContent->koef = $koef;
        $waybillContent->save();
        return ['success' => true, 'koef' => $koef, 'quantity' => $quan];
    }

    /**
     * integration: Накладная (привязана к заказу) - Добавление позиции
     *
     * @param array $post
     * @throws \Exception
     * @return array
     */
    public function createWaybillContent(array $post): array
    {
        if (!isset($post['waybill_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_id");
        }

        $waybill = Waybill::findOne(['id' => $post['waybill_id']]);
        if (!$waybill) {
            throw new BadRequestHttpException("waybill not found");
        }
        if (!$waybill->order_id) {
            throw new BadRequestHttpException("empty order_id");
        }

        $waybillContent = new WaybillContent();
        if (isset($post['waybill_id'])) {
            $waybillContent->waybill_id = $post['waybill_id'];
        }
        if (isset($post['vat_waybill'])) {
            $waybillContent->vat_waybill = (float)$post['vat_waybill'];
        }
        if (isset($post['outer_unit_id'])) {
            $waybillContent->outer_unit_id = (float)$post['outer_unit_id'];
        }
        if (isset($post['quantity_waybill'])) {
            $waybillContent->quantity_waybill = (int)$post['quantity_waybill'];
        }
        if (isset($post['outer_product_id'])) {
            $waybillContent->outer_product_id = $post['outer_product_id'];
        }

        if (isset($post['price_without_vat'])) {
            $waybillContent->price_without_vat = (int)$post['price_without_vat'];
            if (isset($post['vat_waybill'])) {
                $waybillContent->price_with_vat = (int)($post['price_without_vat'] + ($post['price_without_vat'] * $post['vat_waybill']));
                if (isset($post['quantity_waybill'])) {
                    $waybillContent->sum_without_vat = (int)$post['price_without_vat'] * $post['quantity_waybill'];
                    $waybillContent->sum_with_vat = $waybillContent->price_with_vat * $post['quantity_waybill'];
                }
            }
        }

        $waybillContent->save();

        return ['success' => true, 'waybill_content_id' => $waybillContent->id];
    }

    /**
     * integration: Накладная - Удалить/Убрать позицию
     *
     * @param array $post
     * @throws \Exception|\Throwable
     * @return array
     */
    public function deleteWaybillContent(array $post): array
    {
        if (!isset($post['waybill_content_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_content_id");
        }

        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill content not found");
        }

        $waybillContent->delete();

        return ['success' => true];
    }
}