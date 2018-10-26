<?php

namespace api_web\modules\integration\classes\documents;

use api\common\models\AllMaps;
use api_web\classes\DocumentWebApi;
use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\helpers\CurrencyHelper;
use api_web\modules\integration\classes\Dictionary;
use api_web\modules\integration\interfaces\DocumentInterface;
use api_web\modules\integration\modules\iiko\models\iikoService;
use common\models\Organization;
use common\models\OuterAgent;
use common\models\Waybill as BaseWaybill;

/**
 * Class Waybill
 *
 * @package api_web\modules\integration\classes\documents
 */
class Waybill extends BaseWaybill implements DocumentInterface
{

    /**
     * Порлучение данных из модели
     *
     * @throws \Exception
     * @return mixed
     */
    public function prepare()
    {
        if (empty($this->attributes)) {
            return [];
        }

        $return = [
            "id"          => $this->id,
            "number"      => $this->outer_number_code ? [$this->outer_number_code] : null,
            "type"        => DocumentWebApi::TYPE_WAYBILL,
            "status_id"   => $this->status_id,
            "status_text" => \Yii::t('api_web', 'waybill.' . Registry::$waybill_statuses[$this->status_id]),
            "service_id"  => $this->service_id
        ];

        try {
            $agent = OuterAgent::findOne($this->outer_agent_id);
        } catch (\Throwable $t) {
            // Все нормально, пока что не зарефакторили waybill, потом убрать try{}catch(){} todo_refactoring
            $agent = null;
        }

        if (empty($agent)) {
            $return ["agent"] = null;
        } else {
            $return ["agent"] = [
                "id"   => $agent->id,
                "name" => $agent->name,
            ];
        }

        if (empty($agent)) {
            if (!empty($this->order)) {
                $return ["agent"] = [
                    "id"   => $this->order->vendor_id,
                    "name" => $this->order->vendor->name,
                ];
            }
        } elseif (isset($agent['vendor_id'])) {
            $return["vendor"] = [
                "id"   => $agent->vendor_id,
                "name" => Organization::findOne(['id' => $agent->vendor_id])->name,
            ];
        }

        $return["is_mercury_cert"] = $this->getIsMercuryCert();
        $return["count"] = (int)$this->getTotalCount();
        $return["total_price"] = CurrencyHelper::asDecimal($this->getTotalPrice());
        $return["doc_date"] = date("Y-m-d H:i:s T", strtotime($this->doc_date));
        $return["store"] = null; //todo_refactoring

        return $return;
    }

    /**
     * Загрузка модели и получение данных
     *
     * @param $key
     * @throws \Exception
     * @return array
     */
    public static function prepareModel($key)
    {
        $model = self::findOne(['id' => $key]);
        if ($model === null) {
            return [];
        }
        return $model->prepare();
    }

    /**
     * Сброс привязки позиций накладной к заказу
     *
     * @return bool
     * @throws \Throwable
     */
    public function resetPositions()
    {
        if (isset($this->order)) {
            $transaction = \Yii::$app->db_api->beginTransaction();
            try {
                WaybillContent::updateAll(['order_content_id' => null], 'waybill_id = ' . $this->id);
                $this->status_id = Registry::WAYBILL_RESET;
                if (!$this->save()) {
                    throw new ValidationException($this->getFirstErrors());
                }
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        return true;
    }

    /**
     * Накладная - Детальная информация
     *
     * @param $key
     * @throws \Exception
     * @return array
     */
    public static function prepareDetail($key)
    {
        $model = self::findOne(['id' => $key]);
        if ($model === null) {
            return [];
        }

        $return = [
            "id"          => $model->id,
            "code"        => $model->id,
            "status_id"   => $model->status_id,
            "status_text" => \Yii::t('api_web', 'waybill.' . Registry::$waybill_statuses[$model->status_id]),
        ];

        $agent = (new Dictionary($model->service_id, 'Agent'))->agentInfo($model->outer_agent_id);
        if (empty($agent)) {
            $return ["agent"] = null;
        } else {
            $return ["agent"] = [
                "id"   => $agent['id'],
                "name" => $agent['name'],
            ];
        }

        $return["vendor"] = null;
        if (isset($agent['vendor_id'])) {
            $return["vendor"] = [
                "id"   => $agent['vendor_id'],
                "name" => Organization::findOne(['id' => $agent['vendor_id']])->name,
            ];
        } else {
            if (isset($model->order)) {
                $return["vendor"] = [
                    "id"   => $model->order->vendor_id,
                    "name" => $model->order->vendor->name,
                ];
            }
        }

        $store = (new Dictionary($model->service_id, 'Store'))->storeInfo($model->outer_store_id);
        if (empty($store)) {
            $return ["store"] = null;
        } else {
            $return ["store"] = [
                "id"   => $store['id'],
                "name" => $store['name'],
            ];
        }

        $return["doc_date"] = date("Y-m-d H:i:s T", strtotime($model->doc_date));
        $return["outer_number_additional"] = $model->outer_number_additional;
        $return["outer_number_code"] = $model->outer_number_code;
        $return["payment_delay_date"] = date("Y-m-d H:i:s T", strtotime($model->payment_delay_date));
        $return["outer_note"] = $model->outer_note;

        return $return;
    }

    /**
     * Привязка накладной к заказу
     *
     * @param $order_id
     * @throws \Exception
     */
    public function mapWaybill($order_id)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (isset($this->order_id)) {
                $this->resetPositions();
            } else {
                $this->order_id = $order_id;
            }

            $waybillContents = $this->waybillContents;

            if ($this->service_id == 2) {
                $mainOrg_id = iikoService::getMainOrg($this->acquirer_id);
            }

            foreach ($waybillContents as $row) {
                if (isset($row->outer_product_id)) {
                    continue;
                }

                $client_id = $this->acquirer_id;
                if ($this->service_id == 2) {
                    if ($mainOrg_id != $this->acquirer_id) {
                        if ((AllMaps::findOne("service_id = 2 AND org_id = $client_id AND serviceproduct_id = " . $row->outer_product_id) == null) && (!empty($mainOrg_id))) {
                            $client_id = $mainOrg_id;
                        }
                    }
                }

                $product_id = AllMaps::find()
                    ->select('product_id')
                    ->where("service_id = :service_id AND serviceproduct_id = :serviceproduct_id AND org_id = :org_id and is_active = 1",
                        [':service_id' => $this->service_id, ':serviceproduct_id' => $row->outer_product_id, ':org_id' => $client_id])
                    ->scalar();

                if ($product_id == null) {
                    continue;
                }

                $row->order_content_id = \common\models\OrderContent::find()
                    ->select('id')
                    ->where('order_id = :order_id and product_id = :product_id and is_active = 1', [':order_id' => $order_id, ':product_id' => $product_id])
                    ->scalar();
                $row->save();
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}