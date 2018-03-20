<?php

namespace api_web\classes;

use api_web\components\WebApi;
use common\models\Organization;
use common\models\Request;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;

class RequestWebApi extends WebApi
{
    /**
     * Список заявок
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getList(array $post)
    {
        if ($this->user->organization->type_id == Organization::TYPE_RESTAURANT) {
            return $this->getListClient($post);
        } else {
            throw new BadRequestHttpException('Для вас, заявки в разработке...');
        }
    }

    /**
     * Список заявок для ресторана
     * @param array $post
     * @return array
     */
    public function getListClient(array $post)
    {
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $query = Request::find();
        $query->where(['rest_org_id' => $this->user->organization->id]);

        if (isset($post['search'])) {
            /**
             * Фильтр по статусу
             */
            if (isset($post['search']['status'])) {
                $query->andWhere(['active_status' => (int)$post['search']['status']]);
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query->orderBy(['created_at' => SORT_DESC])->all()
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $models = $dataProvider->models;

        $result = [];
        foreach ($models as $model) {
            $result[] = $this->prepareRequest($model);
        }

        $return = [
            'result' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Информация по заявке
     * @param Request $model
     * @return array
     */
    private function prepareRequest(Request $model)
    {
        return [
            'id' => (int)$model->id,
            "name" => $model->product,
            "status" => (int)$model->active_status,
            "created_at" => date('d.m.Y H:i', strtotime($model->created_at)),
            "category" => $model->categoryName->name,
            "category_id" => $model->category,
            "client" => $this->container->get('MarketWebApi')->prepareOrganization($model->client),
            "vendor" => $this->container->get('MarketWebApi')->prepareOrganization($model->vendor) ?? null,
            "hits" => (int)$model->count_views ?? 0,
            "count_callback" => (int)$model->countCallback ?? 0,
            "urgent" => $model->rush_order ?? 0
        ];
    }
}