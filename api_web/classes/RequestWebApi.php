<?php

namespace api_web\classes;

use yii\data\Pagination;
use common\models\Request;
use yii\helpers\ArrayHelper;
use api_web\components\WebApi;
use common\models\Organization;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
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
     * @throws BadRequestHttpException
     */
    public function getListClient(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('Раздел доступен только для ресторанов...');
        }

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
     * Список заявок для поставщика
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getListVendor(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_SUPPLIER) {
            throw new BadRequestHttpException('Раздел доступен только для поставщиков...');
        }

        $organization = $this->user->organization;
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $query = Request::find()->joinWith('client')->orderBy('id DESC');
        //Массив в доставками
        $deliveryRegions = $organization->deliveryRegionAsArray;
        //Доступные для доставки регионы
        if (!empty($deliveryRegions['allow'])) {
            foreach ($deliveryRegions['allow'] as $row) {
                if (!empty($row['administrative_area_level_1']) && !empty($row['locality'])) {
                    $p = $row['administrative_area_level_1'] . $row['locality'];
                    $query->orWhere('CONCAT(`administrative_area_level_1`, `locality`) = :p', [':p' => $p]);
                } elseif ((empty($row['administrative_area_level_1']) || $row['administrative_area_level_1'] == 'undefined') && !empty($row['locality'])) {
                    $query->orWhere(['=', 'locality', $row['locality']]);
                } elseif (!empty($row['administrative_area_level_1']) && empty($row['locality'])) {
                    $query->orWhere(['=', 'administrative_area_level_1', $row['administrative_area_level_1']]);
                }
            }
        }
        //Условия для исключения доставки с регионов
        if (!empty($deliveryRegions['exclude'])) {
            if (!empty($deliveryRegions['exclude'])) {
                foreach ($deliveryRegions['exclude'] as $row) {
                    if (!empty($row['administrative_area_level_1']) && !empty($row['locality'])) {
                        $p = $row['administrative_area_level_1'] . $row['locality'];
                        $query->andWhere('CONCAT(`administrative_area_level_1`, `locality`) <> :s', [':s' => $p]);
                    } elseif ((empty($row['administrative_area_level_1']) || $row['administrative_area_level_1'] == 'undefined') && !empty($row['locality'])) {
                        $query->andWhere(['!=', 'locality', $row['locality']]);
                    } elseif (!empty($row['administrative_area_level_1']) && empty($row['locality'])) {
                        $query->andWhere(['!=', 'administrative_area_level_1', $row['administrative_area_level_1']]);
                    }
                }
            }
        }

        $query->andWhere(['>=', 'end', new \yii\db\Expression('NOW()')])->andWhere(['active_status' => Request::ACTIVE]);

        /**
         * только мои заявки, на которые откликнулся
         */
        if (isset($post['my_only']) && $post['my_only'] == true) {
            $query->andWhere(['responsible_supp_org_id' => (int)$organization->id]);
        }

        if (isset($post['search'])) {
            /**
             * Фильтр по Категории
             */
            if (isset($post['search']['category'])) {
                $query->andWhere(['category' => (int)$post['search']['category']]);
            }
            /**
             * поиск по продукту
             */
            if (isset($post['search']['product'])) {
                $query->andWhere(['like', 'product', $post['search']['product']]);
            }
            /**
             * только срочные заявки
             */
            if (isset($post['search']['urgent']) && $post['search']['urgent'] == true) {
                $query->andWhere(['rush_order' => 1]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
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
     * Список категорий
     * @return array
     */
    public function getCategoryList()
    {
        $result = [];
        $category = ArrayHelper::map(\common\models\MpCategory::find()->where(['parent' => null])->orderBy('name')->all(), 'id', 'name');

        if (!empty($category)) {
            foreach ($category as $key => $item) {
                $result[] = ['id' => $key, 'name' => \Yii::t('app', $item)];
            }
        }

        return $result;
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