<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;

use api\common\models\merc\MercStockEntry;
use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use yii\base\Model;

class StockEntryList extends Model
{
    public function updateDocumentsList($list) {
        $cache = \Yii::$app->cache;
        $owner_guid = mercDicconst::getSetting('enterprise_guid');

        foreach ($list as $item)
        {

            if(!$cache->get('stockEntryRaw_'.$item->GUID))
                $cache->add('stockEntry_'.$item->GUID, $item,60);

            $unit = dictsApi::getInstance()->getUnitByGuid($item->batch->unit->guid);
            $producer = isset($item->batch->origin->producer->enterprise->uuid) ? cerberApi::getInstance()->getEnterpriseByUuid($item->batch->origin->producer->enterprise->uuid) : null;

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
                'create_date' => $item->createDate,
                'update_date' => $item->updateDate,
                'previous' => $item->previous,
                'next' => $item->next,
                'entryNumber' => $item->entryNumber,
                'product_type' => $item->batch->productType,
                'product_name' => $item->batch->productItem->name,
                'amount' => $item->batch->volume,
                'unit' => $unit->unit->name,
                'gtin' => $item->batch->productItem->globalID,
                'article' => $item->batch->productItem->code,
                'production_date' => MercStockEntry::getDate($item->batch->dateOfProduction),
                'expiry_date' => MercStockEntry::getDate($item->batch->expiryDate),
                'batch_id' => $item->batch->batchID,
                'perishable' =>  (int)$item->batch->perishable,
                'producer_name' => isset($producer) ? ($producer->enterprise->name.'('. $producer->enterprise->address->addressView .')') : null,
                'producer_guid' => $item->batch->origin->producer->enterprise->guid,
                'low_grade_cargo' =>  (int)$item->batch->lowGradeCargo,
                'vsd_uuid' => $item->vetDocument->uuid,
                'raw_data' => serialize($item)
            ]);

            if(!$model->save()) {
                var_dump($model->getErrors());
                throw new \Exception('VSD save error');
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
                $result = $api->getStockEntryChangesList($last_visit, $listOptions);
                $stockEntryList = $result->application->result->any['getStockEntryChangesListResponse']->stockEntryList;
            }
            else
                {
                $result = $api->getStockEntryList($listOptions);
                $stockEntryList = $result->application->result->any['getStockEntryListResponse']->stockEntryList;
            }

            $this->updateDocumentsList($stockEntryList->stockEntry);

            if($stockEntryList->count < $stockEntryList->total)
                $listOptions->offset += $stockEntryList->count;

        } while ($stockEntryList->total > ($stockEntryList->count + $stockEntryList->offset));
    }
}