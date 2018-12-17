<?php

namespace api_web\modules\integration\classes\documents;

use api_web\classes\DocumentWebApi;
use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\helpers\CurrencyHelper;
use api_web\helpers\OuterProductMapHelper;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\Organization;
use common\models\OuterAgent;
use common\models\OuterStore;
use common\models\Waybill as BaseWaybill;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;

/**
 * Class Waybill
 *
 * @package api_web\modules\integration\classes\documents
 */
class Waybill extends BaseWaybill implements DocumentInterface
{

    public $helper;

    public function __construct(array $config = [])
    {
        $this->helper = new OuterProductMapHelper();
        parent::__construct($config);
    }

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
            $status_text = "Status " . $this->status_id;
        }

        $return = [
            "id"                       => (int)$this->id,
            "number"                   => $this->outer_number_code ? [$this->outer_number_code] : [],
            "type"                     => DocumentWebApi::TYPE_WAYBILL,
            "status_id"                => (int)$this->status_id,
            "status_text"              => $status_text,
            "service_id"               => (int)$this->service_id,
            "vendor"                   => null,
            "agent"                    => null,
            "store"                    => null,
            "is_mercury_cert"          => $this->getIsMercuryCert(),
            "count"                    => (int)$this->getTotalCount(),
            "total_price"              => CurrencyHelper::asDecimal($this->getTotalPrice()),
            "total_price_with_out_vat" => CurrencyHelper::asDecimal($this->getTotalPriceWithOutVat()),
            "doc_date"                 => WebApiHelper::asDatetime($this->doc_date),
            "outer_number_code"        => $this->outer_number_code ?? null,
            "outer_number_additional"  => $this->outer_number_additional ?? null,
        ];

        $agent = OuterAgent::findOne([
            'id' => $this->outer_agent_id,
            'org_id' => $this->acquirer_id,
            'service_id' => $this->service_id
        ]);
        if (!empty($agent)) {
            $return["agent"] = [
                "id"   => (int)$agent->id,
                "name" => $agent->name,
            ];
            if (!empty($agent->vendor_id)) {
                $return["vendor"] = [
                    "id"   => (int)$agent->vendor_id,
                    "name" => Organization::findOne($agent->vendor_id)->name
                ];
            }
        }

        if (empty($return['vendor'])) {
            if (!empty($this->order)) {
                $return["vendor"] = [
                    "id"   => (int)$this->order->vendor_id,
                    "name" => $this->order->vendor->name,
                ];
            }
        }

        $store = OuterStore::findOne([
            'id'         => $this->outer_store_id,
            'org_id'     => $this->acquirer_id,
            'service_id' => $this->service_id
        ]);
        if (!empty($store)) {
            $return ["store"] = [
                "id"   => (int)$store->id,
                "name" => $store->name,
            ];
        }

        return $return;
    }

    /**
     * Загрузка модели и получение данных
     *
     * @param $key
     * @param $serviceId
     * @throws \Exception
     * @return array
     */
    public static function prepareModel($key, $serviceId = null)
    {
        $where = ['id' => $key];
        if (!is_null($serviceId)) {
            $where['service_id'] = $serviceId;
        }
        $model = self::findOne($where);
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
        //Если нет связи с заказом
        if (!isset($this->order)) {
            throw new BadRequestHttpException("document_has_not_path_to_order");
        }
        /** @var Transaction $transaction */
        $transaction = \Yii::$app->db_api->beginTransaction();
        try {
            WaybillContent::updateAll(['order_content_id' => null], 'waybill_id = :wid', [':wid' => $this->id]);
            $this->status_id = Registry::WAYBILL_RESET;
            if (!$this->save()) {
                throw new ValidationException($this->getFirstErrors());
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
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

        $pd = $model->payment_delay_date;
        if (empty($pd) || $pd == '0000-00-00 00:00:00') {
            $pd = null;
        } else {
            $pd = WebApiHelper::asDatetime($model->payment_delay_date);
        }

        $return = $model->prepare();
        $return['outer_number_additional'] = $model->outer_number_additional;
        $return['outer_number_code'] = $model->outer_number_code;
        $return['payment_delay_date'] = $pd;
        $return['outer_note'] = $model->outer_note;
        $return['code'] = $model->id;

        return $return;
    }
}