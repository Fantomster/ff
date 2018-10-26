<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 8/29/2018
 * Time: 1:11 PM
 */

namespace api_web\helpers;

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use common\helpers\DBNameHelper;
use common\models\IntegrationSettingValue;
use common\models\licenses\License;
use common\models\Order;
use common\models\OrderContent;
use common\models\OuterProductMap;
use common\models\Waybill;
use common\models\WaybillContent;
use yii\web\BadRequestHttpException;

/**
 * Waybills class for generate\update\delete\ actions
 * */
class WaybillHelper
{

    /**
     * @var OuterProductMapHelper
     */
    private $helper;

    /**
     * WaybillHelper constructor.
     */
    public function __construct()
    {
        $this->helper = new OuterProductMapHelper();
    }

    /**
     * Create waybill and waybill_content and binding VSD
     *
     * @param string $uuid VSD uuid
     * @return boolean
     * */
    public function createWaybillFromVsd($uuid)
    {
        $transaction = \Yii::$app->db_api->beginTransaction();
        $orgId = (\Yii::$app->user->identity)->organization_id;
        $modelWaybill = new Waybill();
        $modelWaybill->acquirer_id = $orgId;
        $modelWaybill->service_id = Registry::MERC_SERVICE_ID;

        $modelWaybillContent = new WaybillContent();
        try {
            $modelWaybill->save();
            $modelWaybillContent->waybill_id = $modelWaybill->id;
            $modelWaybillContent->save();
            $transaction->commit();
        } catch (\Throwable $t) {
            $transaction->rollBack();
            \Yii::error($t->getMessage(), __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * @param      $order_id
     * @param null $arOrderContentForCreate С EDI может приходить несколькими файлами orderContent для одного заказа
     * @param null $supplierOrgId
     * @throws \Exception
     * @return mixed
     */
    public function createWaybill($order_id, $arOrderContentForCreate = null, $supplierOrgId = null)
    {
        $order = Order::findOne($order_id);
        if (!$order) {
            throw new BadRequestHttpException('Not found order with id' . $order_id);
        }
        if (is_null($arOrderContentForCreate)) {
            $arOrderContentForCreate = $order->orderContent;
        }
        $licenses = License::getAllLicense($order->client_id, [Registry::RK_SERVICE_ID, Registry::IIKO_SERVICE_ID], true);

        foreach ($licenses as $license) {
            $serviceId = $license['service_id'];
            $settingAuto = IntegrationSettingValue::getSettingsByServiceId($serviceId, $order->client_id, ['auto_unload_invoice']);
            if ($settingAuto) {
                $waybillContents = WaybillContent::find()->andWhere(['order_content_id' => array_keys
                ($order->orderContent)])->indexBy('order_content_id')->all();
                $notInWaybillContent = array_diff_key($arOrderContentForCreate, $waybillContents);

                if ($notInWaybillContent) {
                    $mainOrg = IntegrationSettingValue::getSettingsByServiceId($serviceId, $order->client_id, ['main_org']);
                    $waybillIds = [];
                    try {
                        $rows = $this->helper->getMapForOrder($order, $serviceId, $mainOrg);
                    } catch (\Throwable $t) {
                        \Yii::error($t->getMessage(), 'waybill_create');
                    }

                    if (!empty($rows)) {
                        $arMappedForStores = [];
                        // Remap for 1 store = 1 waybill
                        foreach ($rows as $row) {
                            $arMappedForStores[$row['outer_store_id']][$row['product_id']] = $row;
                        }

                        foreach ($arMappedForStores as $storeId => $storeProducts) {
                            if (!$storeId){
                                $storeId = IntegrationSettingValue::getSettingsByServiceId($serviceId, $order->client_id,
                                    ['defStore']);
                                if (!$storeId){
                                    continue;
                                }
                            }
                            $arOuterMappedProducts = $this->prepareStoreProducts($storeProducts, $notInWaybillContent);
                            $waybillIds[] = $this->createWaybillAndContent($arOuterMappedProducts, $order->client_id,
                                $storeId, $serviceId);
                        }
                        return $waybillIds;
                    }
                    //Agent default store
//                    if ($supplierOrgId) {
//                        $defaultAgent = OuterAgent::findOne(['vendor_id' => $supplierOrgId, 'org_id' => $order->client_id]);
//                        if ($defaultAgent && $defaultAgent->store_id) {
//                            $waybillId = $this->createWaybillAndContent($notInWaybillContent, $order->client_id,
//                                $defaultAgent->store_id, $defaultAgent->service_id);
//                            return [$waybillId];
//
//                        }
//                    }
//                    $hasDefaultStore = 1234;
//                    $hasDefaultServiceID = 2;
//                    if ($hasDefaultStore) {
//                        $waybillId = $this->createWaybillAndContent($notInWaybillContent, $order->client_id,
//                            $hasDefaultStore, $hasDefaultServiceID);
//                        return [$waybillId];
//                    }

                }
            }
        }
        return false;
    }

    /**
     * @param int $orgId
     * @return \common\models\Waybill
     */
    private function buildWaybill($orgId)
    {
        $model = new Waybill();
        $model->acquirer_id = $orgId;
        $model->doc_date = \gmdate('Y-m-d H:i:s');

        return $model;
    }

    /**
     * @param      $arOuterMappedProducts
     * @param      $orgId
     * @param null $outerStoreId
     * @param null $serviceId
     * @return int
     * @throws \Exception
     */
    private function createWaybillAndContent($arOuterMappedProducts, $orgId, $outerStoreId, $serviceId)
    {
        $model = $this->buildWaybill($orgId);
        $model->outer_store_id = (string)$outerStoreId;
        $model->service_id = $serviceId;
        $model->status_id = Registry::WAYBILL_COMPARED;

        //для каждого может быть разный
        $model->edi_number = $this->generateEdiNumber($arOuterMappedProducts, $serviceId);

        $transaction = \Yii::$app->db_api->beginTransaction();

        try {
            if (!$model->save()) {
                throw new ValidationException($model->getErrors());
            }

            foreach ($arOuterMappedProducts as $mappedProduct) {
                /**@var OrderContent $ordCont */
                $ordCont = $mappedProduct['orderContent'];
                $price = $ordCont->price;
                $quantity = $ordCont->quantity;
                $taxRate = $mappedProduct['vat'];
                $priceWithVat = (float)($price + ($price * ($taxRate / 100)));

                $modelWaybillContent = new WaybillContent();
                $modelWaybillContent->order_content_id = $ordCont->id;
                $modelWaybillContent->waybill_id = $model->id;
                $modelWaybillContent->merc_uuid = $ordCont->merc_uuid;
                $modelWaybillContent->outer_product_id = $mappedProduct['outer_product_id'];
                $modelWaybillContent->quantity_waybill = $quantity;
                $modelWaybillContent->vat_waybill = $taxRate;
                $modelWaybillContent->sum_with_vat = $quantity * $priceWithVat;
                $modelWaybillContent->sum_without_vat = $quantity * $price;
                $modelWaybillContent->price_with_vat = $priceWithVat;
                $modelWaybillContent->price_without_vat = $price;
                $modelWaybillContent->koef = $mappedProduct['coefficient'];
                if (!$modelWaybillContent->save()) {
                    throw new ValidationException($modelWaybillContent->getErrors());
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $model->id;
    }

    /**
     * Check if exist row with $uuid
     *
     * @param string $uuid
     * @return boolean
     * */
    public function checkWaybillForVsdUuid($uuid)
    {
        return WaybillContent::find()
            ->leftJoin(DBNameHelper::getMainName() . '.`' . OrderContent::tableName() . '` as oc', 'oc.id = order_content_id')
            ->where(['oc.merc_uuid' => $uuid])
            ->exists();
    }

    /**
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function createWaybillForApi($request)
    {
        if (empty($request['order_id'])) {
            throw new BadRequestHttpException('empty_param|order_id');
        }
        $result = $this->createWaybill($request['order_id']);

        return [
            'result' => $result
        ];
    }

    /**
     * @param $request
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function moveOrderContentToWaybill($request)
    {
        if (!isset($request['waybill_id']) && !isset($request['order_content_id'])) {
            throw new BadRequestHttpException('empty_param|waybill_id|order_content_id');
        }
        $waybill = Waybill::findOne([
            'id'        => $request['waybill_id'],
            'status_id' => [
                Registry::WAYBILL_COMPARED,
                Registry::WAYBILL_ERROR,
                Registry::WAYBILL_FORMED,
            ]]);
        if (!$waybill) {
            throw new BadRequestHttpException('waybill cannot adding waybill_content with id ' . $request['waybill_id']);
        }
        $orderContent = OrderContent::findOne($request['order_content_id']);
        if (!$orderContent) {
            throw new BadRequestHttpException('OrderContent dont exists with id ' . $request['order_content_id']);
        }

        $this->checkOrderForWaybillContent($waybill, $orderContent);

        $taxRate = $orderContent->vat_product ?? null;
        $quantity = $orderContent->quantity;
        $price = $orderContent->price;
        if ($taxRate) {
            $priceWithVat = $price + ($price * ($taxRate / 100));
        }

        $outerProductMap = OuterProductMap::find()->where(['organization_id' => \Yii::$app->user->identity->organization_id])
            ->andWhere(['service_id' => $waybill->service_id, 'product_id' => $orderContent->product_id])
            ->andWhere(['outer_store_id' => $waybill->outer_store_id])->one();

        try {
            $waybillContent = new WaybillContent();
            $waybillContent->waybill_id = $request['waybill_id'];
            $waybillContent->order_content_id = $orderContent->id;
            $waybillContent->outer_product_id = $outerProductMap->outer_product_id ?? $orderContent->product_id;
            $waybillContent->quantity_waybill = (float)$quantity;
            $waybillContent->vat_waybill = $taxRate;
            $waybillContent->merc_uuid = $orderContent->merc_uuid;
            $waybillContent->sum_with_vat = (int)(isset($priceWithVat) ? $priceWithVat * $quantity * 100 : null);
            $waybillContent->sum_without_vat = (int)($price * $quantity * 100);
            $waybillContent->price_with_vat = (int)(isset($priceWithVat) ? $priceWithVat * 100 : null);
            $waybillContent->price_without_vat = (int)($price * 100);
            $waybillContent->save();
            if (!$waybillContent->validate() || !$waybillContent->save()) {
                throw new ValidationException($waybillContent->getErrorSummary(true));
            }
        } catch (\Throwable $t) {
            \Yii::error($t->getMessage());
            return ['result' => $t->getMessage()];
        }

        return ['result' => true];
    }

    /**
     * Проверка на правильность добавления позиции заказа к накладной
     * Нельзя добавить к накладной, имеющей позиции из одного заказа, позиции из другой заказа
     * Так же нельзя добавить позицию заказа, уже имеющую позицию в накладной
     *
     * @param Waybill      $waybill
     * @param OrderContent $orderContent
     * @throws BadRequestHttpException
     */
    private function checkOrderForWaybillContent(Waybill $waybill, OrderContent $orderContent)
    {
        if ($orderContent->waybillContent) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'waybill.order_content_allready_has_waybill_content') . '-' . $orderContent->waybillContent->id);
        }
        $waybillContent = WaybillContent::find()->where(['waybill_id' => $waybill->id])
            ->andWhere(['not', ['order_content_id' => null]])->one();
        if ($waybillContent) {
            $orderContentFromWaybill = $waybillContent->orderContent;
            if ($orderContent->order_id != $orderContentFromWaybill->order_id) {
                throw new BadRequestHttpException(\Yii::t('api_web', 'waybill.order_content_not_for_this_waybill'));
            }
        }
    }

    /**
     * @param $storeProducts
     * @param $notInWaybillContent
     * @return array
     */
    private function prepareStoreProducts($storeProducts, $notInWaybillContent)
    {
        $arStoreProducts = [];
        foreach ($notInWaybillContent as $item) {
            /**@var OrderContent $item */
            if (array_key_exists($item->product_id, $storeProducts)) {
                $outer_product_id = $storeProducts[$item->product_id]['master_serviceproduct_id'] ??
                    $storeProducts[$item->product_id]['outer_product_id'];
                $arStoreProducts[] = [
                    'product_id'       => $item->product_id,
                    'outer_store_id'   => $storeProducts[$item->product_id]['outer_store_id'],
                    'vat'              => $storeProducts[$item->product_id]['vat'],
                    'coefficient'      => $storeProducts[$item->product_id]['coefficient'],
                    'outer_product_id' => $outer_product_id,
                    'orderContent'     => $item,
                ];
            }
        }

        return $arStoreProducts;
    }

    /**
     * @param $arOuterStoreProducts
     * @param $serviceId
     * @return array|string
     */
    private function generateEdiNumber($arOuterStoreProducts, $serviceId)
    {
        /**@var OrderContent $orderContent */
        $orderContent = current($arOuterStoreProducts)['orderContent'];
        $tmp_ed_num = $orderContent->order_id;
        if ($orderContent->edi_number) {
            $tmp_ed_num = $orderContent->edi_number;
        }

        $existWaybill = OrderContent::find()->where(['like', 'edi_number', $tmp_ed_num])
            ->andWhere(['order_id' => $orderContent->order_id])
            ->orderBy(['edi_number' => SORT_DESC])->limit(1)->one();
        if (!$existWaybill) {
            $existWaybill = Waybill::find()->where(['like', 'edi_number', $tmp_ed_num])
                ->andWhere(['service_id' => $serviceId])
                ->orderBy(['edi_number' => SORT_DESC])->limit(1)->one();
        }
        $ed_num = $tmp_ed_num . '-1';

        if ($existWaybill) {
            if (mb_strpos($existWaybill->edi_number, '-')) {
                $ed_num = $this->getLastEdiNumber($existWaybill->edi_number);
            }
        }

        return $ed_num;
    }

    /**
     * @param $ediNumber
     * @return int|mixed|string
     */
    private function getLastEdiNumber($ediNumber)
    {
        $ed_nums = explode('-', $ediNumber);
        $ed_num = array_pop($ed_nums);
        $ed_num = (int)$ed_num + 1;
        array_push($ed_nums, $ed_num);
        $ed_num = implode('-', $ed_nums);

        return $ed_num;
    }
}