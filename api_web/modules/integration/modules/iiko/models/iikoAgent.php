<?php

namespace api_web\modules\integration\modules\iiko\models;

use api\common\models\iiko\iikoWaybill;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\interfaces\ServiceInterface;
use common\models\Order;
use common\models\search\OrderSearch;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class iikoAgent extends WebApi
{
    /**
     * iiko: Список контрагентов синхронизированных из внешней системы
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getAgentsList(array $post)
    {
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);
        $organizationID = $this->user->organization_id;
        $iikoAgents = \api\common\models\iiko\iikoAgent::find()->where(['org_id' => $organizationID]);

        $count = $iikoAgents->count();
        $agentsList = $iikoAgents->limit($pageSize)->offset($pageSize * ($page - 1))->all();

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
            $arr['agents'][$i]['is_active'] = $item->is_active;
            $arr['agents'][$i]['comment'] = $item->comment;
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
     * iiko: Обновление данных для связи контрагента
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function updateAgentData(array $post): array
    {
        $agent = \api\common\models\iiko\iikoAgent::findOne(['id' => $post['agent_id']]);
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

}