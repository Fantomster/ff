<?php

namespace frontend\modules\clientintegr\modules\merc\helpers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocument;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentByUUIDRequest;
use yii\base\Model;

class vetDocumentsChangeList extends Model
{
    public function updateDocumentsList($list) {
        $cache = \Yii::$app->cache;
        $guid = mercDicconst::getSetting('enterprise_guid');

        foreach ($list as $item)
        {
            if($item->vetDType == getVetDocumentByUUIDRequest::DOC_TYPE_PRODUCTIVE)
                continue;

            if(!$cache->get('vetDocRaw_'.$item->uuid))
                $cache->add('vetDocRaw_'.$item->uuid, $item,60);

            $unit = dictsApi::getInstance()->getUnitByGuid($item->certifiedConsignment->batch->unit->guid);
            $sender= cerberApi::getInstance()->getEnterpriseByUuid($item->certifiedConsignment->consignor->enterprise->uuid);
            $recipient = cerberApi::getInstance()->getEnterpriseByUuid($item->certifiedConsignment->consignor->enterprise->uuid);

            $model = MercVsd::findOne(['uuid' => $item->uuid, 'guid' => $guid]);

            if($model == null)
                $model = new MercVsd();

            $model->setAttributes([
                'uuid' => $item->uuid,
                'number' => MercVsd::getNumber($item->issueSeries, $item->issueNumber),
                'date_doc' => $item->issueDate,
                'type' => $item->vetDType,
                'form' => $item->vetDForm,
                'status' => $item->vetDStatus,
                'recipient_name' => $recipient->enterprise->name.'('. $recipient->enterprise->address->addressView .')',
                'recipient_guid' => $recipient->enterprise->guid,
                'sender_guid' => $sender->enterprise->guid,
                'sender_name' =>  $sender->enterprise->name.'('. $sender->enterprise->address->addressView .')',
                'finalized' => $item->finalized,
                'last_update_date' => MercVsd::getDate($item->lastUpdateDate),
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
                'perishable' => $item->certifiedConsignment->batch->perishable,
                'producer_name' => $item->certifiedConsignment->batch->origin->producer->enterprise->name,
                'producer_guid' => $item->certifiedConsignment->batch->origin->producer->enterprise->guid,
                'low_grade_cargo' => $item->certifiedConsignment->batch->lowGradeCargo,
                'raw_data' => serialize($item)
            ]);

            if(!$model->save()) {
                throw new \Exception('VSD save error');
            }

        }
    }

    public function updateData($last_visit)
    {
        $api = mercuryApi::getInstance();

        $result = $api->getVetDocumentChangeList($last_visit);

        if(isset($result->application->result->any['getVetDocumentChangesListResponse']->vetDocumentList->vetDocument))
            $this->updateDocumentsList($result->application->result->any['getVetDocumentChangesListResponse']->vetDocumentList->vetDocument);
    }
}