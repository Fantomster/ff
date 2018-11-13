<?php

namespace api_web\modules\integration\modules\rkeeper\models;

use api\common\models\RkProduct;
use yii\data\Pagination;
use api_web\components\WebApi;
use yii\data\ArrayDataProvider;
use yii\web\BadRequestHttpException;

class rkeeperProduct extends WebApi
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

        $model = RkProduct::findOne((int)$post['id']);
        if (empty($model)) {
            throw new BadRequestHttpException('Not found product');
        }

        return $this->prepareProduct($model);
    }

    /**
     * Список продуктов полученных из rkeeper
     * @param $post
     * @return array
     */
    public function list($post)
    {
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $search = RkProduct::find()->where(['acc' => $this->user->organization->id]);

        if (isset($post['search'])) {
            if (isset($post['search']['name'])) {
                $search->andWhere(['like', 'denom', ':name', [':name' => $post['search']['name']]]);
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
     * @param \api\common\models\RkProduct $model
     * @return array
     */
    private function prepareProduct(RkProduct $model)
    {
        return [
            'id' => (int)$model->id,
            'name' => $model->denom,
            'acc' => $model->acc,
            'rid' => $model->rid,
            'group_name' => $model->group_name,
            'group_rid' => (int)$model->group_rid,
            'product_type' => $model->type,
            'unit' => $model->unitname,
            'unit_rid' => $model->unit_rid,
        ];
    }
}