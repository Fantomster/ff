<?php

namespace api_web\modules\integration\modules\iiko\models;

use api\common\models\iiko\iikoStore as iikoStore_AR;
use api_web\components\WebApi;

class iikoStore extends WebApi
{
    /**
     * Список складов с поиском по наименованию
     * @param $param
     * @return array
     */
    public function list($param)
    {
        $query = iikoStore_AR::find()->where(['org_id' => $this->user->organization->id]);

        if (isset($param['search'])) {
            if (isset($param['search']['name'])) {
                $query->andWhere(['like', 'denom', ':d', [':d' => $param['search']['name']]]);
            }
            if (isset($param['search']['is_active'])) {
                $query->andWhere(['is_active' => $param['search']['is_active']]);
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
     * @param \api\common\models\iiko\iikoStore $model
     * @return array
     */
    private function prepareStore(\api\common\models\iiko\iikoStore $model)
    {
        return [
            'id' => (int)$model->id,
            'name' => $model->denom ?? 'not name',
            'comment' => $model->comment ?? '',
            'is_active' => (int)$model->is_active
        ];
    }
}