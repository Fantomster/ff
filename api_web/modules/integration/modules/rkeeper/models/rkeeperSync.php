<?php

namespace api_web\modules\integration\modules\rkeeper\models;

use api\common\models\RkDic;
use api_web\components\WebApi;


class rkeeperSync extends WebApi
{
    /**
     * Список справочников
     * @return array
     */
    public function list()
    {
        $result = [];
        $models = RkDic::find()->where(['org_id' => $this->user->organization->id])->all();

        foreach ($models as $model) {
            $result[] = [
                'name' => $model->dictype->denom,
                'status' => $model->dicstatus->denom,
                'updated_at' => $model->updated_at,
                'obj_count' => $model->obj_count,
                'type' => $model->dictype_id
            ];
        }

        return $result;
    }
}