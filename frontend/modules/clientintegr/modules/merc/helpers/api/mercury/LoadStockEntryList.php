<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercStockEntry;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\Cerber;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\ikar\ikarApi;
use yii\base\Model;
use yii\helpers\BaseStringHelper;
use yii\helpers\Json;

class LoadStockEntryList extends Model
{
    public $org_id;

    public function updateDocumentsList($list) {
        $owner_guid = mercDicconst::getSetting('enterprise_guid', $this->org_id);
        $list = is_array($list) ? $list : [$list];

        foreach ($list as $item)
        {
            $unit = dictsApi::getInstance($this->org_id)->getUnitByGuid($item->batch->unit->guid);
            $producer = isset($item->batch->origin->producer->enterprise->uuid) ? cerberApi::getInstance($this->org_id)->getEnterpriseByUuid($item->batch->origin->producer->enterprise->uuid) : null;
            $country = isset($item->batch->origin->country->guid) ? ikarApi::getInstance($this->org_id)->getCountryByGuid($item->batch->origin->country->guid) : null;
            $model = MercStockEntry::findOne(['guid' => $item->guid]);

            if($model == null)
                $model = new MercStockEntry();
            $model->setAttributes([
                'uuid' => $item->uuid,
                'guid' => $item->guid,
                'owner_guid' => $owner_guid,
                'active' => (int)$item->active,
                'last' => (int)$item->last,
                'status' => $item->status,
                'create_date' => date('Y-m-d h:i:s',strtotime($item->createDate)),
                'update_date' => date('Y-m-d h:i:s',strtotime($item->updateDate)),
                'previous' => isset($item->previous) ? $item->previous : null,
                'next' => isset($item->next) ? $item->next : null,
                'entryNumber' => $item->entryNumber,
                'product_type' => $item->batch->productType,
                'product_name' => $item->batch->productItem->name,
                'amount' => $item->batch->volume,
                'unit' => isset($unit) ? $unit->name : null,
                'gtin' => $item->batch->productItem->globalID,
                'article' => $item->batch->productItem->code,
                'production_date' => MercStockEntry::getDate($item->batch->dateOfProduction),
                'expiry_date' => MercStockEntry::getDate($item->batch->expiryDate),
                'batch_id' => is_array($item->batch->batchID) ? json_encode($item->batch->batchID) : $item->batch->batchID,
                'perishable' =>  (int)$item->batch->perishable,
                'producer_name' => isset($producer) ? ($producer->name.'('. $producer->address->addressView .')') : null,
                'producer_country' => isset($country) ? $country->name : null,
                'producer_guid' => isset($item->batch->origin->producer->enterprise->guid) ? $item->batch->origin->producer->enterprise->guid : null,
                'low_grade_cargo' =>  (int)$item->batch->lowGradeCargo,
                'vsd_uuid' => isset($item->vetDocument) ? $item->vetDocument->uuid : null,
                'product_marks' => isset($item->batch->packageList->package->productMarks->_) ? $item->batch->packageList->package->productMarks->_ : "",
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
                $result = $api->getStockEntryChangesList($last_visit, $listOptions);
                $stockEntryList = $result->application->result->any['getStockEntryChangesListResponse']->stockEntryList;
            $count += $stockEntryList->count;
            $this->log('Load '.$count.' / '. $stockEntryList->total.PHP_EOL);
            if($stockEntryList->count > 0)
                $this->updateDocumentsList($stockEntryList->stockEntry);

            if($stockEntryList->count < $stockEntryList->total)
                $listOptions->offset += $stockEntryList->count;

        } while ($stockEntryList->total > ($stockEntryList->count + $stockEntryList->offset));
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