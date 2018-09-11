<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\helpers\WebApiHelper;
use common\models\AllService;
use common\models\search\OrderSearch;
use common\models\Order;
use yii\data\Pagination;

/**
 * Class EdoWebApi
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-11
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */
class EdoWebApi extends WebApi
{

    /**
     * История заказов
     * @param array $post
     * @return array
     */
    public function orderHistory(array $post)
    {
        $sort_field = (!empty($post['sort']) ? $post['sort'] : null);
        $page = (!empty($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (!empty($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $search = new OrderSearch();

        WebApiHelper::clearRequest($post);

        if (isset($post['search'])) {

            $search->service_id = (AllService::findOne(['denom' => 'EDI']))->id;

            if (isset($post['search']['vendor']) && !empty($post['search']['vendor'])) {
                $search->vendor_array = $post['search']['vendor'];
            }

            /**
             * Статусы
             */
            if (isset($post['search']['status']) && !empty($post['search']['status'])) {
                $search->status_array = (array)$post['search']['status'];
            }

            /**
             * Фильтр по дате создания
             */
            if (isset($post['search']['create_date']) && !empty($post['search']['create_date'])) {
                if (isset($post['search']['create_date']['start']) && !empty($post['search']['create_date']['start'])) {
                    $search->date_from = $post['search']['create_date']['start'];
                }

                if (isset($post['search']['create_date']['end']) && !empty($post['search']['create_date']['end'])) {
                    $search->date_to = $post['search']['create_date']['end'];
                }
            }

            /**
             * Фильтр по дате завершения
             */
            if (isset($post['search']['completion_date']) && !empty($post['search']['completion_date'])) {
                if (isset($post['search']['completion_date']['start']) && !empty($post['search']['completion_date']['start'])) {
                    $search->completion_date_from = $post['search']['completion_date']['start'];
                }

                if (isset($post['search']['completion_date']['end']) && !empty($post['search']['completion_date']['end'])) {
                    $search->completion_date_to = $post['search']['completion_date']['end'];
                }
            }
        }

        $search->client_id = $this->user->organization_id;

        $dataProvider = $search->search(null);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);
        /**
         * Сортировка по полям
         */
        if (!empty($post['sort'])) {

            $field = $post['sort'];
            $sort = SORT_ASC;

            if (strstr($post['sort'], '-') !== false) {
                $field = str_replace('-', '', $field);
                $sort = SORT_DESC;
            }

            if ($field == 'vendor') {
                $field = 'vendor_id';
            }

            if ($field == 'create_user') {
                $field = 'created_by_id';
            }

            $dataProvider->setSort(['defaultOrder' => [$field => $sort]]);
        }


        /**
         * Собираем результат
         */
        $orders = [];
        $headers = [];
        $models = $dataProvider->models;
        if (!empty($models)) {
            /**
             * @var $model Order
             */
            foreach ($models as $model) {

                if ($model->status == Order::STATUS_DONE) {
                    $date = $model->completion_date ?? $model->actual_delivery;
                } else {
                    $date = $model->updated_at;
                }

                if ($model->completion_date != $date) {
                    $model->completion_date = $date;
                    $model->save(false);
                }

                $date = (!empty($date) ? \Yii::$app->formatter->asDate($date, "dd.MM.yyyy") : null);

                $orders[] = [
                    'id' => (int)$model->id,
                    'created_at' => \Yii::$app->formatter->asDate($model->created_at, "dd.MM.yyyy"),
                    'completion_date' => $date,
                    'status' => (int)$model->status,
                    'status_text' => $model->statusText,
                    'vendor' => $model->vendor->name,
                    'currency_id' => $model->currency_id,
                    'create_user' => $model->createdByProfile->full_name ?? '',
                    'accept_user' => $model->acceptedByProfile->full_name ?? ''
                ];
            }
            if (isset($orders[0])) {
                foreach (array_keys($orders[0]) as $key) {
                    $headers[$key] = (new Order())->getAttributeLabel($key);
                }
            }
        }

        $return = [
            'headers' => $headers,
            'orders' => $orders,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ],
            'sort' => $sort_field
        ];

        return $return;
    }


}