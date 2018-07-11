<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocument;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentByUUIDRequest;
use yii\base\Model;

class VetDocumentsChangeList extends Model
{
    public function updateDocumentsList($list) {
        $cache = \Yii::$app->cache;
        $guid = mercDicconst::getSetting('enterprise_guid');

        foreach ($list as $item)
        {
            if($item->vetDType == MercVsd::DOC_TYPE_PRODUCTIVE)
                continue;

            if(!$cache->get('vetDocRaw_'.$item->uuid))
                $cache->add('vetDocRaw_'.$item->uuid, $item,60);

            $unit = dictsApi::getInstance()->getUnitByGuid($item->certifiedConsignment->batch->unit->guid);
            $sender= cerberApi::getInstance()->getEnterpriseByUuid($item->certifiedConsignment->consignor->enterprise->uuid);
            $recipient = cerberApi::getInstance()->getEnterpriseByUuid($item->certifiedConsignment->consignee->enterprise->uuid);
            $producer = isset($item->certifiedConsignment->batch->origin->producer->enterprise->uuid) ? cerberApi::getInstance()->getEnterpriseByUuid($item->certifiedConsignment->batch->origin->producer->enterprise->uuid) : null;

            $model = MercVsd::findOne(['uuid' => $item->uuid]);

            if($model == null)
                $model = new MercVsd();

            var_dump($item->certifiedConsignment->batch->batchID);

            $model->setAttributes([
                'uuid' => $item->uuid,
                'number' => (isset($item->issueSeries) && (isset($item->issueNumber))) ? MercVsd::getNumber($item->issueSeries, $item->issueNumber) : null,
                'date_doc' => $item->issueDate,
                'type' => $item->vetDType,
                'form' => $item->vetDForm,
                'status' => $item->vetDStatus,
                'recipient_name' => $recipient->enterprise->name.'('. $recipient->enterprise->address->addressView .')',
                'recipient_guid' => $recipient->enterprise->guid,
                'sender_guid' => $sender->enterprise->guid,
                'sender_name' =>  $sender->enterprise->name.'('. $sender->enterprise->address->addressView .')',
                'finalized' => $item->finalized,
                'last_update_date' => ($item->lastUpdateDate != "-") ? date('Y-m-d h:i:s',strtotime($item->lastUpdateDate)) : null,
                'vehicle_number' => $item->certifiedConsignment->transportInfo->transportNumber->vehicleNumber,
                'trailer_number' => $item->certifiedConsignment->transportInfo->transportNumber->trailerNumber,
                'container_number' => $item->certifiedConsignment->transportInfo->transportNumber->containerNumber,
                'transport_storage_type' => $item->certifiedConsignment->transportStorageType,
                'product_type' => $item->certifiedConsignment->batch->productType,
                'product_name' => $item->certifiedConsignment->batch->productItem->name,
                'amount' => $item->certifiedConsignment->batch->volume,
                'unit' => $unit->unit->name,
                'gtin' => $item->certifiedConsignment->batch->productItem->globalID,
                'article' => $item->certifiedConsignment->batch->productItem->code,
                'production_date' => MercVsd::getDate($item->certifiedConsignment->batch->dateOfProduction),
                'expiry_date' => MercVsd::getDate($item->certifiedConsignment->batch->expiryDate),
                'batch_id' => $item->certifiedConsignment->batch->batchID,
                'perishable' =>  (int)$item->certifiedConsignment->batch->perishable,
                'producer_name' => isset($producer) ? ($producer->enterprise->name.'('. $producer->enterprise->address->addressView .')') : null,
                'producer_guid' => $item->certifiedConsignment->batch->origin->producer->enterprise->guid,
                'low_grade_cargo' =>  (int)$item->certifiedConsignment->batch->lowGradeCargo,
                'raw_data' => serialize($item)
            ]);

            if(!$model->save()) {
                Yii::error(serialize($model->getErrors()));
            }

        }
    }

    public function updateData($last_visit)
    {
        $api = mercuryApi::getInstance();
        $listOptions = new ListOptions();
        $listOptions->count = 100;
        $listOptions->offset = 0;

        do {
            if (isset($last_visit)) {
                $result = $api->getVetDocumentChangeList($last_visit, $listOptions);
                $vetDocumentList = $result->application->result->any['getVetDocumentChangesListResponse']->vetDocumentList;
            }
            else
                {
                $result = $api->getVetDocumentList(null, $listOptions);
                $vetDocumentList = $result->application->result->any['getVetDocumentListResponse']->vetDocumentList;
            }

            $this->updateDocumentsList($vetDocumentList->vetDocument);

            if($vetDocumentList->count < $vetDocumentList->total)
                $listOptions->offset += $vetDocumentList->count;

        } while ($vetDocumentList->total > ($vetDocumentList->count + $vetDocumentList->offset));
    }

    public function handUpdateData($vsd_uuid_list)
    {
        $mask = '/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/';
        preg_match_all($mask, $vsd_uuid_list, $list);
        $list = $list[0];
        if(count($list) == 0)
            return false;

        $api = mercuryApi::getInstance();

        foreach ($list as $item)
        {
            $vsd = trim($item);
            $result[] = $api->getVetDocumentByUUID($vsd);
            $this->updateDocumentsList($result);
        }

        return true;
    }
}