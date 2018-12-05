<?php

namespace api_web\modules\integration\modules\one_s\models;

use api\common\models\one_s\OneSStore;
use api_web\components\WebApi;

class one_sStore extends WebApi
{
    /**
     * Список складов с поиском по наименованию
     * @param $param
     * @return array
     */
    public function list($param)
    {
        $query = OneSStore::find()->where(['org_id' => $this->user->organization->id]);
        if (isset($param['search'])) {
            if (isset($param['search']['name'])) {
                $query->andWhere(['like', 'name', ':d', [':d' => $param['search']['name']]]);
            }
        }
        $models = $query->all();
        $result = [];
        if (!empty($models)) {
            foreach ($models as $model) {
                $result[] = $this->prepareStore($model);
            }
        }
        return $result;
    }

    /**
     * @param OneSStore $model
     * @return array
     */
    private function prepareStore(\api\common\models\one_s\OneSStore $model)
    {
        return [
            'id' => (int)$model->id,
            'name' => $model->name ?? 'not name',
            'cid' => $model->cid ?? '',
            'address' => $model->address ?? '',
            'org_id' => $model->org_id
        ];
    }
}