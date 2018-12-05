<?php

namespace api_web\modules\integration\modules\one_s\models;

use api\common\models\one_s\one_sWaybill;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use yii\web\HttpException;

class one_sAgent extends WebApi
{
    /**
     * one_s: Список контрагентов синхронизированных из внешней системы
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getAgentsList(array $post)
    {
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);
        $organizationID = $this->user->organization_id;
        $one_sAgents = \api\common\models\one_s\OneSContragent::find()->where(['org_id' => $organizationID]);

        $count = $one_sAgents->count();
        $agentsList = $one_sAgents->limit($pageSize)->offset($pageSize * ($page - 1))->all();

        $arr = [];
        $i = 0;
        foreach ($agentsList as $item) {
            $arr['agents'][$i]['id'] = $item->id;
            $arr['agents'][$i]['uuid'] = $item->uuid ?? 'Не указано';
            $arr['agents'][$i]['org_id'] = $item->org_id ?? null;
            $arr['agents'][$i]['org_denom'] = $item->organization->name ?? 'Не указано';
            $arr['agents'][$i]['denom'] = $item->denom ?? 'Не указано';
            $arr['agents'][$i]['store_denom'] = $item->store->denom ?? 'Не указано';
            $arr['agents'][$i]['vendor_name'] = $item->vendor->name ?? 'Не указано';
            $i++;
        }

        $arr['pagination'] = [
            'page' => $page,
            'total_page' => ceil($count / $pageSize),
            'page_size' => $pageSize
        ];
        return $arr;
    }


    /**
     * one_s: Обновление данных для связи контрагента
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function updateAgentData(array $post): array
    {
        $agent = \api\common\models\one_s\one_sAgent::findOne(['id' => $post['agent_id']]);
        if(!$agent){
            throw new HttpException('No such agent');
        }
        $agent->vendor_id = (int)$post['vendor_id'];
        $agent->store_id = (int)$post['store_id'];
        if (!$agent->validate() || !$agent->save()) {
            throw new ValidationException($agent->getFirstErrors());
        }
        return [
          "success" => true
        ];
    }


    /**
     * one_s: Создание сопоставлений номенклатуры накладной с продуктами MixCart
     * @param array $post
     * @return array
     */
    public function handleWaybillData(array $post): array
    {
        $waybillData = new one_sWaybillData();
        $waybillData->waybill_id = $post['waybill_id'];
        $waybillData->product_id = $post['product_id'];
        $waybillData->product_rid = $post['product_rid'];
        $waybillData->munit = $post['munit'];
        $waybillData->org = $post['org'];
        $waybillData->vat = $post['vat'];
        $waybillData->vat_included = $post['vat_included'];
        $sum = 0 + str_replace(',', '.', $post['sum']);
        $waybillData->sum = $sum;
        $quant = 0 + str_replace(',', '.', $post['quant']);
        $waybillData->quant = $quant;
        $waybillData->defsum = $post['defsum'];
        $waybillData->defquant = $post['defquant'];
        $koef = 0 + str_replace(',', '.', $post['koef']);
        $waybillData->koef = $koef;
        $waybillData->linked_at = $post['linked_at'];

        if (!$waybillData->validate() || !$waybillData->save()) {
            throw new ValidationException($waybillData->getFirstErrors());
        }

        return [
            "success" => true,
            "waybill_data_id" => $waybillData->id
        ];
    }

}