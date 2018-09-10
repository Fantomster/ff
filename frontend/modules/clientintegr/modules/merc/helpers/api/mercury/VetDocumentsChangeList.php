<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;

use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use yii\base\Model;
use yii\helpers\BaseStringHelper;

class VetDocumentsChangeList extends Model
{
    public $org_id;

    public function updateDocumentsList($list) {
        $list = is_array($list) ? $list : [$list];
        foreach ($list as $item)
        {
            if($item->vetDType == MercVsd::DOC_TYPE_PRODUCTIVE)
                continue;

            $unit = dictsApi::getInstance($this->org_id)->getUnitByGuid($item->certifiedConsignment->batch->unit->guid);
            $sender= cerberApi::getInstance($this->org_id)->getEnterpriseByUuid($item->certifiedConsignment->consignor->enterprise->uuid);
            $recipient = cerberApi::getInstance($this->org_id)->getEnterpriseByUuid($item->certifiedConsignment->consignee->enterprise->uuid);


            $producer = isset($item->certifiedConsignment->batch->origin->producer) ? MercVsd::getProduccerData($item->certifiedConsignment->batch->origin->producer, $this->org_id) : null;
            $model = MercVsd::findOne(['uuid' => $item->uuid]);

            if($model == null)
                $model = new MercVsd();

            $model->setAttributes([
                'uuid' => $item->uuid,
                'number' => (isset($item->issueSeries) && (isset($item->issueNumber))) ? MercVsd::getNumber($item->issueSeries, $item->issueNumber) : null,
                'date_doc' => date('Y-m-d h:i:s',strtotime($item->issueDate)),
                'type' => $item->vetDType,
                'form' => $item->vetDForm,
                'status' => $item->vetDStatus,
                'recipient_name' => !isset($recipient) ? null : $recipient->name.'('. $recipient->address->addressView .')',
                'recipient_guid' => $item->certifiedConsignment->consignee->enterprise->guid,
                'sender_guid' => $item->certifiedConsignment->consignor->enterprise->guid,
                'sender_name' =>  !isset($sender) ? null : $sender->name.'('. $sender->address->addressView .')',
                'finalized' => $item->finalized,
                'last_update_date' => ($item->lastUpdateDate != "-") ? date('Y-m-d h:i:s',strtotime($item->lastUpdateDate)) : null,
                'vehicle_number' => isset($item->certifiedConsignment->transportInfo->transportNumber->vehicleNumber) ? $item->certifiedConsignment->transportInfo->transportNumber->vehicleNumber : null,
                'trailer_number' => isset($item->certifiedConsignment->transportInfo->transportNumber->trailerNumber) ? $item->certifiedConsignment->transportInfo->transportNumber->trailerNumber : null,
                'container_number' => isset($item->certifiedConsignment->transportInfo->transportNumber->containerNumber) ? $item->certifiedConsignment->transportInfo->transportNumber->containerNumber : null,
                'transport_storage_type' => $item->certifiedConsignment->transportStorageType,
                'product_type' => $item->certifiedConsignment->batch->productType,
                'product_name' => $item->certifiedConsignment->batch->productItem->name,
                'amount' => $item->certifiedConsignment->batch->volume,
                'unit' => !isset($unit) ? null : $unit->name,
                'gtin' => $item->certifiedConsignment->batch->productItem->globalID,
                'article' => $item->certifiedConsignment->batch->productItem->code,
                'production_date' => MercVsd::getDate($item->certifiedConsignment->batch->dateOfProduction),
                'expiry_date' => MercVsd::getDate($item->certifiedConsignment->batch->expiryDate),
                'batch_id' => !is_array($item->certifiedConsignment->batch->batchID) ? $item->certifiedConsignment->batch->batchID : implode(", ", $item->certifiedConsignment->batch->batchID),
                'perishable' =>  (int)$item->certifiedConsignment->batch->perishable,
                'producer_name' => isset($producer) ? serialize($producer['name']) : null,
                'producer_guid' => isset($producer) ? serialize($producer['guid']) : null,
                'low_grade_cargo' =>  (int)$item->certifiedConsignment->batch->lowGradeCargo,
                'raw_data' => serialize($item)
            ]);
            $model->save(false);
        }
    }

    public function updateData($last_visit)
    {
        $api = mercuryApi::getInstance($this->org_id);
        $listOptions = new ListOptions();
        $listOptions->count = 100;
        $listOptions->offset = 0;
        $count = 0;
        $this->log('Load'.PHP_EOL);

        do {
                $result = $api->getVetDocumentChangeList($last_visit, $listOptions);
                $vetDocumentList = $result->application->result->any['getVetDocumentChangesListResponse']->vetDocumentList;
            $count += $vetDocumentList->count;
            $this->log('Load '.$count.' / '. $vetDocumentList->total.PHP_EOL);

            if ($vetDocumentList->count > 0)
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

        $api = mercuryApi::getInstance($this->org_id);

        foreach ($list as $item)
        {
            $vsd = trim($item);
            $result[] = $api->getVetDocumentByUUID($vsd);
            $this->updateDocumentsList($result);
        }

        return true;
    }

    /**
     * @param $message array|string
     */
    public function log($message)
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $message = $message . PHP_EOL;
        $message .= str_pad('', 80, '=') . PHP_EOL;
        $className = BaseStringHelper::basename(get_class($this));
        file_put_contents(\Yii::$app->basePath . "/runtime/daemons/logs/jobs_" . $className . '.log', $message, FILE_APPEND);
    }
}