<?php

namespace api_web\modules\integration\modules\vetis\api\mercury;

use api\common\models\merc\MercVsd;
use common\models\vetis\VetisUnit;
use api_web\modules\integration\modules\vetis\api\cerber\cerberApi;
use yii\base\Model;
use yii\helpers\BaseStringHelper;

/**
 * Class VetDocumentsChangeList
 *
 * @package api_web\modules\integration\modules\vetis\api\mercury
 */
class VetDocumentsChangeList extends Model
{
    /**
     * @var int organization id
     */
    public $org_id;

    /**
     * @param $list
     */
    public function updateDocumentsList($list)
    {
        $list = is_array($list) ? $list : [$list];
        $i = 0;
        foreach ($list as $item) {
            $i++;
            if ($item->vetDType == MercVsd::DOC_TYPE_PRODUCTIVE) {
                continue;
            }

            $unit = VetisUnit::findOne(['guid' => $item->certifiedConsignment->batch->unit->guid, 'active' => true, 'last' => true]);
            VetisUnit::getUpdateData(0);
            $sender = cerberApi::getInstance($this->org_id)->getEnterpriseByUuid($item->certifiedConsignment->consignor->enterprise->uuid);
            $recipient = cerberApi::getInstance($this->org_id)->getEnterpriseByUuid($item->certifiedConsignment->consignee->enterprise->uuid);

            $producer = isset($item->certifiedConsignment->batch->origin->producer) ? MercVsd::getProduccerData($item->certifiedConsignment->batch->origin->producer, $this->org_id) : null;

            $model = MercVsd::findOne(['uuid' => $item->uuid]);

            if ($model == null) {
                $model = new MercVsd();
            }

            $model->setAttributes([
                'uuid'                   => $item->uuid,
                'number'                 => (isset($item->issueSeries) && (isset($item->issueNumber))) ? MercVsd::getNumber($item->issueSeries, $item->issueNumber) : null,
                'date_doc'               => date('Y-m-d h:i:s', strtotime($item->issueDate)),
                'type'                   => $item->vetDType,
                'form'                   => $item->vetDForm,
                'status'                 => $item->vetDStatus,
                'recipient_name'         => !isset($recipient) ? null : $recipient->name . ' (' . $recipient->address->addressView . ')',
                'recipient_guid'         => $item->certifiedConsignment->consignee->enterprise->guid,
                'sender_guid'            => $item->certifiedConsignment->consignor->enterprise->guid,
                'sender_name'            => !isset($sender) ? null : $sender->name . ' (' . $sender->address->addressView . ')',
                'finalized'              => $item->finalized,
                'last_update_date'       => ($item->lastUpdateDate != "-") ? date('Y-m-d h:i:s', strtotime($item->lastUpdateDate)) : null,
                'transport_storage_type' => $item->certifiedConsignment->transportStorageType,
                'product_type'           => $item->certifiedConsignment->batch->productType,
                'product_name'           => $item->certifiedConsignment->batch->productItem->name,
                'amount'                 => $item->certifiedConsignment->batch->volume,
                'unit'                   => !isset($unit) ? null : $unit->name,
                'gtin'                   => $item->certifiedConsignment->batch->productItem->globalID,
                'article'                => $item->certifiedConsignment->batch->productItem->code,
                'production_date'        => MercVsd::getDate($item->certifiedConsignment->batch->dateOfProduction),
                'expiry_date'            => MercVsd::getDate($item->certifiedConsignment->batch->expiryDate),
                'batch_id'               => !is_array($item->certifiedConsignment->batch->batchID) ? $item->certifiedConsignment->batch->batchID : implode(", ", $item->certifiedConsignment->batch->batchID),
                'perishable'             => (int)$item->certifiedConsignment->batch->perishable,
                'producer_name'          => isset($producer) ? $producer['name'][0] : null,
                'producer_guid'          => isset($producer) ? $producer['guid'][0] : null,
                'low_grade_cargo'        => (int)$item->certifiedConsignment->batch->lowGradeCargo,
                'raw_data'               => serialize($item),
                'owner_guid'             => isset($item->certifiedConsignment->batch->owner) ? $item->certifiedConsignment->batch->owner->guid : null,
                'product_guid'           => isset($item->certifiedConsignment->batch->product->guid) ? $item->certifiedConsignment->batch->product->guid : null,
                'sub_product_guid'       => isset($item->certifiedConsignment->batch->subProduct->guid) ? $item->certifiedConsignment->batch->subProduct->guid : null,
                'product_item_guid'      => isset($item->certifiedConsignment->batch->productItem->guid) ? $item->certifiedConsignment->batch->productItem->guid : null,
                'origin_country_guid'    => isset($item->certifiedConsignment->batch->origin->country->guid) ? $item->certifiedConsignment->batch->origin->country->guid : null,
                'confirmed_by'           => isset($item->statusChange->specifiedPerson) ? json_encode(!is_array($item->authentication->laboratoryResearch) ? [$item->authentication->laboratoryResearch] : $item->authentication->laboratoryResearch) : null,
                'other_info'             => json_encode([
                    'locationProsperity' => isset($item->authentication->locationProsperity) ? $item->authentication->locationProsperity : null,
                    'cargoExpertized'    => isset($item->authentication->cargoExpertized) ? $item->authentication->cargoExpertized : null,
                    'specialMarks'       => isset($item->authentication->specialMarks) ? $item->authentication->specialMarks : null
                ]),
                'laboratory_research'    => isset($item->authentication->laboratoryResearch) ? json_encode($item->authentication->laboratoryResearch) : null,
                'transport_info'         => isset($item->certifiedConsignment->transportInfo) ? json_encode($item->certifiedConsignment->transportInfo) : null,
                'unit_guid'              => isset($item->certifiedConsignment->batch->unit->guid) ? $item->certifiedConsignment->batch->unit->guid : null
            ]);

            if (isset($item->referencedDocument)) {
                $docs = null;
                if (!is_array($item->referencedDocument)) {
                    $docs[] = $item->referencedDocument;
                } else {
                    $docs = $item->referencedDocument;
                }

                foreach ($docs as $doc) {
                    if (($doc->type >= 1) && ($doc->type <= 5)) {
                        $model->waybill_number = (isset($doc->issueSeries) && (isset($doc->issueNumber))) ? MercVsd::getNumber($doc->issueSeries, $doc->issueNumber) : null;
                        $model->waybill_date = isset($doc->issueDate) ? $doc->issueDate : null;
                        break;
                    }
                }
            }

            $model->save(false);
        }
    }

    /**
     * @param $last_visit
     */
    public function updateData($last_visit)
    {
        $api = mercuryApi::getInstance($this->org_id);
        $listOptions = new ListOptions();
        $listOptions->count = 100;
        $listOptions->offset = 0;
        $count = 0;
        $this->log('Load' . PHP_EOL);

        do {
            $result = $api->getVetDocumentChangeList($last_visit, $listOptions);
            $vetDocumentList = $result->application->result->any['getVetDocumentChangesListResponse']->vetDocumentList;
            $count += $vetDocumentList->count;
            $this->log('Load ' . $count . ' / ' . $vetDocumentList->total . PHP_EOL);

            if ($vetDocumentList->count > 0) {
                $this->updateDocumentsList($vetDocumentList->vetDocument);
            }

            if ($vetDocumentList->count < $vetDocumentList->total) {
                $listOptions->offset += $vetDocumentList->count;
            }

        } while ($vetDocumentList->total > ($vetDocumentList->count + $vetDocumentList->offset));
    }

    /**
     * @param $vsd_uuid_list
     * @return bool
     */
    public function handUpdateData($vsd_uuid_list)
    {
        $mask = '/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/';
        preg_match_all($mask, $vsd_uuid_list, $list);
        $list = $list[0];
        if (count($list) == 0) {
            return false;
        }

        $api = mercuryApi::getInstance($this->org_id);

        foreach ($list as $item) {
            $vsd = trim($item);
            $result[] = $api->getVetDocumentByUUID($vsd);
            $this->updateDocumentsList($result);
        }

        return true;
    }

    /**
     * @param array|string $message
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