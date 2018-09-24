<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 8/29/2018
 * Time: 1:11 PM
 */

namespace api_web\helpers;

use common\helpers\DBNameHelper;
use common\models\OrderContent;
use common\models\OuterStore;
use common\models\Waybill;
use common\models\WaybillContent;
use yii\helpers\ArrayHelper;

/**
 * Waybills class for generate\update\delete\ actions
 * */
class WaybillHelper
{
    /**@var int const for mercuriy service id in all_service table */
    const MERC_SERVICE_ID = 4;
    const EDI_SERVICE_ID = 6;

    /**
     * Create waybill and waybill_content and binding VSD
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
     * @throws \Exception
     * */
    public function createWaybill($order_id)
    {
        $order = \common\models\Order::findOne(['id' => $order_id]);

        if (!$order) {
            throw new \Exception('Нет заказа с номером ' . $order_id);
        }
        $dbName = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);

        $stories = OrderContent::find()->select('m.store_rid, order_content.product_id')
            ->leftJoin('`'.$dbName .'`.all_map m', 'order_content.product_id = m.product_id AND m.service_id IN (1, 2) AND m.org_id = ' . $order->client_id)
            ->where('order_content.order_id = ' . $order_id)->groupBy('m.store_rid')
            ->indexBy('product_id')->all();
        var_dump(current($order->orderContent));
//        $stories = ArrayHelper::getColumn($stories, 'store_rid');
        $waybill = WaybillContent::findOne(['order_content_id' => current($order->orderContent)])->waybill;

        if (!empty($stories)) {
            foreach ($stories as $store) {
                $store_uuid = (OuterStore::findOne($store))->outer_uid;
                $hasWaybill = Waybill::find()->where(['id' => $waybill->id, 'outer_store_uuid' => $store_uuid]);
                if (!$hasWaybill) {
                    $model = $this->buildWaybill($order);
                    $model->outer_store_uuid = $store_uuid;

                    if (!$model->save()) {
                        \yii::error('Error during saving auto waybill' . print_r($model->getErrors(), true));
                        continue;
                    }
                }
            }
        } else {
            if (!$waybill) {
                $model = $this->buildWaybill($order);

                if (!$model->save()) {
                    \yii::error('Error during saving auto waybill' . print_r($model->getErrors(), true));
                }
            }
        }

    }

    private function buildWaybill($order)
    {
        $model = new Waybill();
        $model->acquirer_id = $order->client_id;
        $model->service_id = WaybillHelper::EDI_SERVICE_ID;
        $datetime = new \DateTime();
        $model->doc_date = $datetime->format('Y-m-d H:i:s');

        return $model;
    }

    private function getWaybillId($orgId)
    {
//        foreach ($order->orderContent as $orderContent) {
//            $index = $orderContent->id;
//            $hasWaybillContent = WaybillContent::findOne(['order_content_id' => $index]);
//            if (!$hasWaybill && !$isOrderSp && !$hasWaybillContent) {
//                $modelWaybillContent = new WaybillContent();
//                $modelWaybillContent->order_content_id = $index;
//                $modelWaybillContent->merc_uuid = $arr[$index]['VETID'] ?? null;
//                $modelWaybillContent->waybill_id = $modelWaybill->id;
//                $modelWaybillContent->product_outer_id = $index;
//                $modelWaybillContent->quantity_waybill = (float)($arr[$index]['ACCEPTEDQUANTITY'] ?? null);
//                $modelWaybillContent->price_waybill = (float)($arr[$index]['PRICE'] ?? null);
//                $modelWaybillContent->vat_waybill = (float)($arr[$index]['PRICEWITHVAT'] ?? null);
//                $modelWaybillContent->save();
//            }
//        }
    }

    /**
     * Check if exist row with $uuid
     * @param string $uuid
     * @return boolean
     * */
    public function checkWaybillForVsdUuid($uuid)
    {
        return WaybillContent::find()->where(['merc_uuid' => $uuid])->exists();
    }
}