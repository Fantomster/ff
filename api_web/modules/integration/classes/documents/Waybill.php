<?php
namespace api_web\modules\integration\classes\documents;

use api_web\modules\integration\classes\Dictionary;
use api_web\modules\integration\classes\Document;
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
        if (empty($model)) {
            return [];
        }

        $return = [
            "id" => $model->id,
            "type" => Document::TYPE_WAYBILL,
            "status_id" => $model->bill_status_id,
            "status_text" => "",
        ];

        $agent = (new Dictionary($model->service_id, 'Agent'))->agentInfo($model->outer_contractor_uuid);

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
        $return["is_mercury_cert"] = $model->getIsMercuryCert();
        $return["count"] = $model->getTotalCount();
        $return["total_price"] = $model->getTotalPrice();
        $return["doc_date"] = date("Y-m-d H:i:s T", strtotime($model->doc_date));

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
}