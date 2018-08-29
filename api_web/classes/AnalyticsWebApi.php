<?php

namespace api_web\classes;

use common\models\Order;
use api_web\components\WebApi;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;
use yii\db\Query;

/**
 * Class AnalyticsWebApi
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-28
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */
class AnalyticsWebApi extends WebApi
{

    /**
     * Ресторан: Статистика по товарам
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function clientGoods($post)
    {

        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $whereParams = ['order.client_id' => $this->user->organization->id];

        // фильтр - поставщик
        if (isset($post['search']['vendor_id'])) {
            $whereParams['order.vendor_id'] = $post['search']['vendor_id'];
        }
        // фильтр - менеджер
        if (isset($post['search']['employee_id'])) {
            $whereParams['order_assignment.assigned_to'] = $post['search']['employee_id'];
        }
        // фильтр - статус заказа
        $statuses = [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            Order::STATUS_PROCESSING,
            Order::STATUS_DONE,
            Order::STATUS_FORMING,
        ];
        $whereParams['order.status'] = $statuses;
        if (isset($post['search']['order_status_id']) && is_array($post['search']['order_status_id'])) {
            $whereParams['order.status'] = $post['search']['order_status_id'];
        }
        // фильтр - валюта
        if (isset($post['search']['currency_id'])) {
            $whereParams['currency.id'] = $post['search']['currency_id'];
        }
        $query = new Query;
        $query->select(
            [
                'order.created_at',
                'currency.id',
                'order.status',
                'order_assignment.assigned_to',
                'order.vendor_id',
                'catalog_base_goods.product as name',
                'FORMAT(SUM(order_content.quantity), 2) AS count',
                'FORMAT(SUM(order_content.quantity * order_content.price), 2) AS total',
                'order.currency_id AS currency_id',
                'currency.symbol AS currency',
            ]
        )->from('order_content')
            ->leftJoin('catalog_base_goods', 'catalog_base_goods.id = order_content.product_id')
            ->leftJoin('order', 'order.id = order_content.order_id')
            ->leftJoin('currency', 'currency.id = order.currency_id')
            ->andWhere($whereParams)
            ->groupBy('order_content.product_id')->orderBy(['total' => SORT_ASC]);


        if (isset($post['search']['date']['from'])) {
            $query->andWhere('order.created_at >= :date_from',
                [':date_from' => date('Y-m-d H:i:s', strtotime($post['search']['date']['from'] . ' 00:00:00'))]);
        }
        if (isset($post['search']['date']['to'])) {
            $query->andWhere('order.created_at <= :date_to',
                [':date_to' => date('Y-m-d H:i:s', strtotime($post['search']['date']['to'] . ' 23:59:59'))]);
        }

        if (isset($post['search']['employee_id'])) {
            $query->leftJoin('order_assignment', 'order.id = order_assignment.order_id');
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query->all()
        ]);
        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);
        return [
            'result' => $dataProvider->models,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

    }

}