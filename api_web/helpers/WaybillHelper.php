<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 8/29/2018
 * Time: 1:11 PM
 */

namespace api_web\helpers;

use common\helpers\DBNameHelper;
use common\models\Order;
use common\models\OrderContent;
use common\models\OuterAgent;
use common\models\OuterStore;
use common\models\Waybill;
use common\models\WaybillContent;

/**
 * Waybills class for generate\update\delete\ actions
 * */
class WaybillHelper
{
    /**@var int const for mercuriy service id in all_service table */
    const MERC_SERVICE_ID = 4;
    /**@var int const for EDI service id in all_service table */
    const EDI_SERVICE_ID = 6;
    //TODO:translate
    const WAYBILL_COMPARED = 'compared';
    const WAYBILL_FORMED = 'formed';
    const WAYBILL_ERROR = 'error';
    const WAYBILL_RESET = 'reset';
    const WAYBILL_UNLOADED = 'unloaded';
    const WAYBILL_UNLOADING = 'unloading';

    /**@var array $statuses */
    static $statuses = [
        self::WAYBILL_COMPARED  => 1,
        self::WAYBILL_FORMED    => 2,
        self::WAYBILL_ERROR     => 3,
        self::WAYBILL_RESET     => 4,
        self::WAYBILL_UNLOADED  => 5,
        self::WAYBILL_UNLOADING => 6,
    ];

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
        $modelWaybill->service_id = self::MERC_SERVICE_ID;

        $modelWaybillContent = new WaybillContent();
        $modelWaybillContent->merc_uuid = $uuid;
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
     * @param \common\models\Order $order
     * @param                      $arIdsForCreate
     * @param                      $supplierOrgId
     * @return array|bool
     */
    public function createWaybill(Order $order, $arIdsForCreate, $supplierOrgId): array
    {
        $settingsAuto = true;
        if ($settingsAuto) {
            $waybillContents = WaybillContent::find()->andWhere(['order_content_id' => array_keys
            ($order->orderContent)])->indexBy('order_content_id')->all();
            $notInWaybillContent = array_diff_key($arIdsForCreate, $waybillContents);

            if ($notInWaybillContent) {
                $defaultAgent = OuterAgent::findOne(['vendor_id' => $supplierOrgId, 'org_id' => $order->client_id]);
                if ($defaultAgent && $defaultAgent->store_id) {
                    $waybillId = $this->createWaybillAndContent($notInWaybillContent, $order->client_id,
                        $defaultAgent->store_id, $defaultAgent->service_id);
                    return [$waybillId];

                }

                $hasDefaultStore = 1234;
                $hasDefaultServiceID = 1234;
                if ($hasDefaultStore) {
                    $waybillId = $this->createWaybillAndContent($notInWaybillContent, $order->client_id,
                        $hasDefaultStore, $hasDefaultServiceID);
                    return [$waybillId];
                }

                $integrations = ['iiko' => 2];
                foreach ($integrations as $integration) {
                    $dbName = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
                    $stories = OrderContent::find()->select([
                        'm.store_rid as store_id',
                        'GROUP_CONCAT(order_content.product_id) as prd_ids'])
                        ->leftJoin('`' . $dbName . '`.all_map m', 'order_content.product_id = m.product_id AND m.service_id = ' . $integration . ' AND m.org_id = ' . $order->client_id)
                        ->where(['order_content.order_id' => $order->id])
                        ->andWhere(['not', ['m.store_rid' => null]])
                        ->groupBy('m.store_rid')->indexBy('m.store_rid')->all();
                    $orderContForStore = [];
                    if (empty($waybillContents)) {
                        if (!empty($stories)) {
                            $waybillIds = [];
                            foreach ($stories as $store) {
                                $store_uuid = (OuterStore::findOne($store['store_rid']))->outer_uid;
                                $prods = explode(',', $store['prd_ids']);

                                foreach ($prods as $prod) {
                                    /**@var OrderContent $ordCont */
                                    foreach ($notInWaybillContent as $ordCont) {
                                        if ($ordCont->product_id == $prod) {
                                            $orderContForStore[$ordCont->id] = $ordCont;
                                        }
                                    }
                                }
                                $waybillIds[] = $this->createWaybillAndContent($orderContForStore, $order->client_id,
                                    $store_uuid, $store_uuid);
                            }
                            return $waybillIds;
                        }
                    }
                }
                $notInWaybillContent = array_diff_key($notInWaybillContent, $orderContForStore);
                if (!empty($notInWaybillContent)){
                    $waybillId = $this->createWaybillAndContent($notInWaybillContent, $order->client_id);
                    return [$waybillId];
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
        $model->service_id = WaybillHelper::EDI_SERVICE_ID;
        $model->bill_status_id = self::$statuses[self::WAYBILL_FORMED]; //TODO: bill_status_id ???
        $model->readytoexport = 0;
        $model->is_deleted = 0;
        $datetime = new \DateTime();
        $model->doc_date = $datetime->format('Y-m-d H:i:s');
        $model->created_at = $datetime->format('Y-m-d H:i:s');
        $model->exported_at = $datetime->format('Y-m-d H:i:s');

        return $model;
    }

    /**
     * @param      $orderContent
     * @param      $orgId
     * @param null $outerStoreUuid
     * @param null $serviceId
     * @return bool|int
     */
    private function createWaybillAndContent($orderContent, $orgId, $outerStoreUuid = null, $serviceId = null){
        $model = $this->buildWaybill($orgId);
        $model->outer_store_uuid = $outerStoreUuid;
        $model->service_id = $serviceId;
        $tmp_ed_num = reset($orderContent)->edi_number;
        $existWaybill = Waybill::find()->where(['like', 'edi_number', $tmp_ed_num])->orderBy(['edi_number' => 'desc'])->limit(1);
        if($existWaybill){
            if(strpos('-', $existWaybill->edi_number)){
                $ed_num = explode('-', $existWaybill->edi_number);
                $ed_num[1] = (int)$ed_num[1] + 1;
                $ed_num = implode('-', $ed_num);
            } else {
                $ed_num = $existWaybill->edi_number . '-1';
            }
        } else {
            $ed_num = $tmp_ed_num;
        }
        $model->edi_number = $ed_num;
        if (!$model->save()) {
            \yii::error('Error during saving waybill' . print_r($model->getErrors(), true));
            return false;
        }

        foreach ($orderContent as $ordCont) {
            $price = $ordCont->price;
            $quantity = $ordCont->quantity;
            $taxRate = $ordCont->vat_product;
            $priceWithVat = (float)($price + ($price * ($taxRate / 100)));

            $modelWaybillContent = new WaybillContent();
            $modelWaybillContent->order_content_id = $ordCont->id;
            $modelWaybillContent->waybill_id = $model->id;
            $modelWaybillContent->merc_uuid = $ordCont->merc_uuid;
            $modelWaybillContent->product_outer_id = $ordCont->product_id;
            $modelWaybillContent->quantity_waybill = $quantity;
            $modelWaybillContent->price_waybill = $price;
            $modelWaybillContent->vat_waybill = $taxRate;
            $modelWaybillContent->sum_with_vat = $quantity * $priceWithVat;
            $modelWaybillContent->sum_without_vat = $quantity * $price;
            $modelWaybillContent->price_with_vat = $priceWithVat;
            $modelWaybillContent->price_without_vat = $price;
            $modelWaybillContent->save();
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
        return WaybillContent::find()->where(['merc_uuid' => $uuid])->exists();
    }
}