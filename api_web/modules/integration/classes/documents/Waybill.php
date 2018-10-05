<?php
namespace api_web\modules\integration\classes\documents;

use api_web\modules\integration\classes\Dictionary;
use api_web\modules\integration\classes\DocumentWebApi;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\Waybill as BaseWaybill;

class Waybill extends BaseWaybill implements DocumentInterface
{

    /**
     * Порлучение данных из модели
     * @return mixed
     */
    public function prepare()
    {
        if (empty($this->attributes)) {
            return [];
        }

        $return = [
            "id" => $this->id,
            "number" => $this->outer_number_code,
            "type" => DocumentWebApi::TYPE_WAYBILL,
            "status_id" => $this->bill_status_id,
            "status_text" => "",
        ];

        $agent = (new Dictionary($this->service_id, 'Agent'))->agentInfo($this->outer_contractor_uuid);

        $return ["agent"] = [
            "uid" => $agent['outer_uid'],
            "name" => $agent['name'],
            "difer" => false,
        ];

        $return["vendor"] = [
            "id" => $agent['vendor_id'],
            "name" => $agent['vendor_name'],
            "difer" => false,
        ];
        $return["is_mercury_cert"] = $this->getIsMercuryCert();
        $return["count"] = $this->getTotalCount();
        $return["total_price"] = $this->getTotalPrice();
        $return["doc_date"] = date("Y-m-d H:i:s T", strtotime($this->doc_date));

        return $return;
    }

    /**
     * Загрузка модели и получение данных
     * @param $key
     * @return $array
     */
    public static function prepareModel($key)
    {
        $model = self::findOne(['id' => $key]);
        if($model === null ) {
            return [];
        }
        return $model->prepare();
    }

    /**
     * Сброс привязки позиций накладной к заказу
     * @return int
     */
    public function resetPositions ()
    {
        return WaybillContent::updateAll(['order_content_id' => null], 'waybill_id = '.$this->id);
    }
      
    /**
     * Накладная - Детальная информация
     * @param $key
     * @return array
     */
    public static function prepareDetail($key)
    {
        $model = self::findOne(['id' => $key]);
        if($model === null ) {
            return [];
        }

        $return = [
            "id" => $model->id,
            "code" => $model->id,
            "status_id" => $model->bill_status_id,
            "status_text" => "",
        ];

        $agent = (new Dictionary($model->service_id, 'Agent'))->agentInfo($model->outer_contractor_uuid);
        $return ["agent"] = [
            "uid" => $agent['outer_uid'],
            "name" => $agent['name'],
        ];

        $return["vendor"] = [
            "id" => $agent['vendor_id'],
            "name" => $agent['vendor_name'],
        ];

        $store = (new Dictionary($model->service_id, 'Store'))->storeInfo($model->outer_store_uuid);
        $return ["store"] = [
            "uid" => $store['outer_uid'],
            "name" => $store['name'],
        ];

        $return["doc_date"] = date("Y-m-d H:i:s T", strtotime($model->doc_date));
        $return["outer_number_additional"] = $model->outer_number_additional;
        $return["outer_number_code"] = $model->outer_number_code;
        $return["payment_delay_date"] = date("Y-m-d H:i:s T", strtotime($model->payment_delay_date));
        $return["outer_note"] = $model->outer_note;

        return $return;
    }

    /**
     * Привязка накладной к заказу
     * @return int
     */
    public function mapWaybill ($order_id, $waybill_id)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            self::updateAll(['order_id' => $order_id], 'waybill_id = '.$waybill_id);


            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}