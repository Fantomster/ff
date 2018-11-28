<?php

namespace api_web\modules\integration\classes\documents;

use api_web\classes\DocumentWebApi;
use api_web\components\Registry;
use api_web\helpers\CurrencyHelper;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\Order as BaseOrder;
use common\models\OrderContent;
use common\models\OuterAgent;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class Order extends BaseOrder implements DocumentInterface
{

    public static $waybill_service_id = null;

    /**
     * Получение данных из модели
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function prepare()
    {
        if (empty($this->attributes)) {
            return [];
        }

        $return = [
            "id"                => (int)$this->id,
            "number"            => $this->ediNumber,
            "type"              => DocumentWebApi::TYPE_ORDER,
            "status_id"         => (int)$this->getGroupStatus(),
            "status_text"       => \Yii::t('api_web', 'doc_group.' . Registry::$doc_group_status[$this->getGroupStatus()]),
            "order_status_text" => $this->statusText,
            "service_id"        => (int)$this->service_id,
            "is_mercury_cert"   => $this->getIsMercuryCert(),
            "count"             => (int)$this->positionCount,
            "total_price"       => CurrencyHelper::asDecimal($this->getTotalPriceFromDb(self::$waybill_service_id)),
            "doc_date"          => date("Y-m-d H:i:s T", strtotime($this->created_at)),
            "vendor"            => null,
            "agent"             => null,
            "store"             => null
        ];

        $vendor = $this->vendor;
        $return["vendor"] = [
            "id"    => (int)$vendor->id,
            "name"  => $vendor->name,
            "difer" => false,
        ];

        $agent = OuterAgent::findOne(['vendor_id' => $vendor->id, 'org_id' => $this->client_id, 'service_id' => self::$waybill_service_id]);
        if (!empty($agent)) {
            $return["agent"] = [
                'id'   => (int)$agent->id,
                'name' => $agent->name,
            ];
        }

        return $return;
    }

    /**
     * @return bool
     */
    public function getIsMercuryCert()
    {
        return (OrderContent::find()->where(['order_id' => $this->id])->andWhere('merc_uuid is not null')->count()) > 0;
    }

    /**
     * Загрузка модели и получение данных
     *
     * @param      $key
     * @param null $serviceId
     * @return array
     * @throws BadRequestHttpException
     */
    public static function prepareModel($key, $serviceId = null)
    {
        if ($serviceId) {
            self::$waybill_service_id = $serviceId;
        }
        $where = ['id' => $key];
        $model = self::findOne($where);
        if ($model === null) {
            return [];
        }
        return $model->prepare();
    }

    /**
     * Групповой статус документа
     *
     * @return int
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function getGroupStatus()
    {
        if (is_null(self::$waybill_service_id)) {
            throw new BadRequestHttpException("empty_param|service_id");
        }
        $waybill_status = ArrayHelper::getColumn($this->getWaybills(self::$waybill_service_id), 'status_id');
        $waybill_status = array_unique($waybill_status);
        $index_group_status = Registry::DOC_GROUP_STATUS_WAIT_SENDING;
        //Если все накладные выгружены
        if ($waybill_status == [Registry::WAYBILL_UNLOADED]) {
            $index_group_status = Registry::DOC_GROUP_STATUS_SENT;
        }
        //Если есть хоть одна в статусе сформирована, или вообще нет накладных
        if (in_array(Registry::WAYBILL_FORMED, $waybill_status) || empty($waybill_status) || count($this->getOrderContentWithOutWaybill()) > 0) {
            $index_group_status = Registry::DOC_GROUP_STATUS_WAIT_FORMING;
        }
        return $index_group_status;
    }
}