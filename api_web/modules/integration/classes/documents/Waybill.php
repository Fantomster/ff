<?php
namespace api_web\modules\integration\classes\documents;

use api\common\models\AllMaps;
use api_web\modules\integration\classes\Dictionary;
use api_web\modules\integration\classes\DocumentWebApi;
use api_web\modules\integration\interfaces\DocumentInterface;
use api_web\modules\integration\modules\iiko\models\iikoService;
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
        if(isset($this->order_id)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                WaybillContent::updateAll(['order_content_id' => null], 'waybill_id = ' . $this->id);
                $this->order_id = null;
                $this->save();
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        return true;
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
    public function mapWaybill ($order_id)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if(isset($this->order_id))
            {
                $this->resetPositions();
            }
            else
            {
                $this->order_id = $order_id;
            }
            
            $waybillContents = $this->waybillContents;

            if ($this->service_id == 2) {
                $mainOrg_id = iikoService::getMainOrg($this->acquirer_id);
            }

            foreach ($waybillContents as $row)
            {
                if(isset($row->product_outer_id)) {
                    continue;
                }

                $client_id = $this->acquirer_id;
                if ($this->service_id == 2) {
                    if ($mainOrg_id != $this->acquirer_id) {
                        if((AllMaps::findOne("service_id = 2 AND org_id = $client_id AND serviceproduct_id = ".$row->product_outer_id) == null) && (!empty($mainOrg_id))) {
                            $client_id = $mainOrg_id;
                        }
                    }
                }

                $product_id = AllMaps::find()
                    ->select('product_id')
                    ->where("service_id = :service_id AND serviceproduct_id = :serviceproduct_id AND org_id = :org_id",
                        [':service_id' => $this->service_id, ':serviceproduct_id' => $row->product_outer_id, ':org_id' => $client_id])
                    ->scalar();

                if($product_id == null) {
                    continue;
                }

                $row->order_content_id = \common\models\OrderContent::find()
                    ->select('id')
                    ->where('order_id = :order_id and product_id = :product_id', [':order_id' => $order_id, ':product_id' => $product_id])
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