<?php

namespace api_web\modules\integration\modules\iiko\models;

use yii\data\Pagination;
use api_web\components\WebApi;
use yii\data\ArrayDataProvider;
use yii\web\BadRequestHttpException;

class iikoProduct extends WebApi
{
    /**
     * Информация о продукте
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function get($post)
    {
        if (empty($post['id'])) {
            throw new BadRequestHttpException('Empty id.');
        }

        $model = \api\common\models\iiko\iikoProduct::findOne((int)$post['id']);
        if (empty($model)) {
            throw new BadRequestHttpException('Not found product');
        }

        return $this->prepareProduct($model);
    }

    /**
     * Список продуктов полученных из iiko
     * @param $post
     * @return array
     */
    public function list($post)
    {
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $search = \api\common\models\iiko\iikoProduct::find()->where(['org_id' => $this->user->organization->id]);

        if (isset($post['search'])) {
            if (isset($post['search']['name'])) {
                $search->andWhere(['like', 'denom', ':name', [':name' => $post['search']['name']]]);
            }
            if (isset($post['search']['is_active'])) {
                $search->andWhere(['is_active' => (int)$post['search']['is_active']]);
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $search->all()
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = $this->prepareProduct($model);
        }

        $return = [
            'products' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Подготовка продукта к выдаче
     * @param \api\common\models\iiko\iikoProduct $model
     * @return array
     */
    private function prepareProduct(\api\common\models\iiko\iikoProduct $model)
    {
        return [
            'id' => (int)$model->id,
            'name' => $model->denom,
            'num' => $model->num,
            'code' => $model->code,
            'cooking_place_type' => $model->cooking_place_type,
            'product_type' => $model->product_type,
            'unit' => $model->unit,
            'is_active' => (int)$model->is_active
        ];
    }
}