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
            $agent = OuterAgent::findOne($this->outer_contractor_id);
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
}