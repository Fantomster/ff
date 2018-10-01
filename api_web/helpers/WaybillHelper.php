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
    const WAYBILL_FORMED = 'Сформирована';
    const WAYBILL_FORMED = 'Сформирована';
    const WAYBILL_FORMED = 'Сформирована';
    const WAYBILL_FORMED = 'Сформирована';
    const WAYBILL_FORMED = 'Сформирована';
    const WAYBILL_FORMED = 'Сформирована';
    static $types = [
        self::WAYBILL_FORMED => 1,
        self::WAYBILL_FORMED => 1,
        self::WAYBILL_FORMED => 1,
        self::WAYBILL_FORMED => 1,
        self::WAYBILL_FORMED => 1,
        self::WAYBILL_FORMED => 1,
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
     * @return array
     */
    public function createWaybill(Order $order) : array
    {
        $dbName = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);

        $stories = OrderContent::find()->select(['m.store_rid as store_id', 'order_content.product_id'])
            ->leftJoin('`' . $dbName . '`.all_map m', 'order_content.product_id = m.product_id AND m.service_id = 2 AND m.org_id = ' . $order->client_id)
            ->where(['order_content.order_id' => $order->id])
            ->andWhere(['not', ['m.store_rid' => null]])
            ->groupBy('m.store_rid')->all();
        $waybillContents = WaybillContent::find()->andWhere(['order_content_id' => array_keys($order->orderContent)])->all();
        $wbModels = [];
        if (empty($waybillContents)) {
            if (!empty($stories)) {
//                throw new Exception(print_r($stories, true));
                foreach ($stories as $store) {
                    $store_uuid = (OuterStore::findOne($store['store_rid']))->outer_uid;
                    $model = $this->buildWaybill($order);
                    $model->outer_store_uuid = $store_uuid;
                    if (!$model->save()) {
                        \yii::error('Error during saving waybill' . print_r($model->getErrors(), true));
                        continue;
                    }
                    $wbModels[] = $model;
                }
            } else {
                $model = $this->buildWaybill($order);
                if (!$model->save()) {
                    \yii::error('Error during saving auto waybill' . print_r($model->getErrors(), true));
                }

                return [$model];
            }
        } else {
            /**@var WaybillContent $wContent */
            foreach ($waybillContents as $wContent) {
                $wbModels[$wContent->waybill_id] = $wContent->waybill;
            }
        }


        return $wbModels;
    }

    /**
     * @param \common\models\Order $order
     * @return \common\models\Waybill
     */
    private function buildWaybill(Order $order)
    {
        $model = new Waybill();
        $model->acquirer_id = $order->client_id;
        $model->service_id = WaybillHelper::EDI_SERVICE_ID;
        $model->bill_status_id = 000; //TODO: bill_status_id ???
        $model->readytoexport = 0;
        $model->is_deleted = 0;
        $datetime = new \DateTime();
        $model->doc_date = $datetime->format('Y-m-d H:i:s');
        $model->created_at = $datetime->format('Y-m-d H:i:s');
        $model->exported_at = $datetime->format('Y-m-d H:i:s');

        return $model;
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