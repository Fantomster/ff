<?php

namespace api_web\modules\integration\classes\documents;

use api_web\classes\DocumentWebApi;
use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\helpers\CurrencyHelper;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\Organization;
use common\models\OuterAgent;
use common\models\OuterStore;
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

        if (isset(Registry::$waybill_statuses[$this->status_id])) {
            $status_text = \Yii::t('api_web', 'waybill.' . Registry::$waybill_statuses[$this->status_id]);
        } else {
            $status_text = "Status 0";
        }

        $return = [
            "id"              => $this->id,
            "number"          => $this->outer_number_code ? [$this->outer_number_code] : null,
            "type"            => DocumentWebApi::TYPE_WAYBILL,
            "status_id"       => $this->status_id,
            "status_text"     => $status_text,
            "service_id"      => $this->service_id,
            "vendor"          => null,
            "agent"           => null,
            "store"           => null,
            "is_mercury_cert" => $this->getIsMercuryCert(),
            "count"           => (int)$this->getTotalCount(),
            "total_price"     => CurrencyHelper::asDecimal($this->getTotalPrice()),
            "doc_date"        => date("Y-m-d H:i:s T", strtotime($this->doc_date))
        ];

        $agent = OuterAgent::findOne(['id' => $this->outer_agent_id]);
        if (!empty($agent)) {
            $return["agent"] = [
                "id"   => $agent->id,
                "name" => $agent->name,
            ];
            if (!empty($agent->vendor_id)) {
                $return["vendor"] = [
                    "id"   => $agent->vendor_id,
                    "name" => Organization::findOne($agent->vendor_id)->name
                ];
            }
        }

        if (empty($return['vendor'])) {
            if (!empty($this->order)) {
                $return["vendor"] = [
                    "id"   => $this->order->vendor_id,
                    "name" => $this->order->vendor->name,
                ];
            }
        }

        $store = OuterStore::findOne(['id' => $this->outer_store_id]);
        if (!empty($store)) {
            $return ["store"] = [
                "id"   => $store->id,
                "name" => $store->name,
            ];
        }

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
        if (empty($model)) {
            return [];
        }

        if (isset(Registry::$waybill_statuses[$model->status_id])) {
            $status_text = \Yii::t('api_web', 'waybill.' . Registry::$waybill_statuses[$model->status_id]);
        } else {
            $status_text = "Status " . $model->status_id;
        }

        $return = [
            "id"                      => $model->id,
            "code"                    => $model->id,
            "status_id"               => $model->status_id,
            "status_text"             => $status_text,
            "agent"                   => null,
            "vendor"                  => null,
            "store"                   => null,
            "doc_date"                => date("Y-m-d H:i:s T", strtotime($model->doc_date)),
            "outer_number_additional" => $model->outer_number_additional,
            "outer_number_code"       => $model->outer_number_code,
            "payment_delay_date"      => !empty($model->payment_delay_date) ? date("Y-m-d H:i:s T", strtotime($model->payment_delay_date)) : null,
            "outer_note"              => $model->outer_note
        ];

        $agent = OuterAgent::findOne(['id' => $model->outer_agent_id]);
        if (!empty($agent)) {
            $return["agent"] = [
                "id"   => $agent->id,
                "name" => $agent->name,
            ];
        }

        if (isset($agent->vendor_id)) {
            $return["vendor"] = [
                "id"   => $agent->vendor_id,
                "name" => Organization::findOne(['id' => $agent->vendor_id])->name,
            ];
        } else {
            if (!empty($model->order)) {
                $return["vendor"] = [
                    "id"   => $model->order->vendor_id,
                    "name" => $model->order->vendor->name,
                ];
            }
        }

        $store = OuterStore::findOne(['id' => $model->outer_store_id]);
        if (!empty($store)) {
            $return["store"] = [
                "id"   => $store->id,
                "name" => $store->name,
            ];
        }

        return $return;
    }
}