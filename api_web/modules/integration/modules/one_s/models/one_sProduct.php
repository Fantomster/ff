<?php

namespace api_web\modules\integration\modules\one_s\models;

use yii\data\Pagination;
use api_web\components\WebApi;
use yii\data\ArrayDataProvider;
use yii\web\BadRequestHttpException;

class one_sProduct extends WebApi
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

        $model = \api\common\models\one_s\one_sProduct::findOne((int)$post['id']);
        if (empty($model)) {
            throw new BadRequestHttpException('Not found product');
        }

        return $this->prepareProduct($model);
    }

    /**
     * Список продуктов полученных из one_s
     * @param $post
     * @return array
     */
    public function list($post)
    {
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $search = \api\common\models\one_s\OneSGood::find()->where(['org_id' => $this->user->organization->id]);

        if (isset($post['search'])) {
            if (isset($post['search']['name'])) {
                $search->andWhere(['like', 'name', ':name', [':name' => $post['search']['name']]]);
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
     *
     * @param \api\common\models\one_s\OneSGood $model
     * @return array
     */
    private function prepareProduct(\api\common\models\one_s\OneSGood $model)
    {
        return [
            'id' => (int)$model->id,
            'name' => $model->name,
            'code' => $model->cid,
            'parent_id' => $model->parent_id,
            'measure' => $model->measure,
            'org_id' => $model->org_id
        ];
    }
}