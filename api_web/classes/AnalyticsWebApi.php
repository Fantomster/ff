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

    const ORDER_STATUSES_WELL = [
        Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
        Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
        Order::STATUS_PROCESSING,
        Order::STATUS_DONE,
        Order::STATUS_FORMING,
    ];

    /**
     * Общий метод
     * @param $post
     * @param $limit int
     * @return array
     * @throws BadRequestHttpException
     */
    public function vendorTurnover($post, $limit = NULL)
    {
        // ограничение на собственные заказы
        $whereParams = ['order.client_id' => $this->user->organization->id];

        // фильтр - поставщик
        if (isset($post['search']['vendor_id'])) {
            $whereParams['order.vendor_id'] = $post['search']['vendor_id'];
        }
        // фильтр - статус заказа
        $whereParams['order.status'] = self::ORDER_STATUSES_WELL;
        if (isset($post['search']['order_status_id']) && is_array($post['search']['order_status_id'])) {
            $whereParams['order.status'] = $post['search']['order_status_id'];
        }
        // фильтр - валюта
        if (isset($post['search']['currency_id'])) {
            $whereParams['currency.id'] = $post['search']['currency_id'];
        }

        // ТЕЛО ЗАПРОСА
        $query = new Query;
        $query->select(
            [
                'organization.name as name',
                'SUM(order_content.quantity * order_content.price) AS total_sum',
                'COUNT(order_content.order_id) AS total_count_order',
            ]
        )->from('order_content')
            ->leftJoin('order', 'order.id = order_content.order_id')
            ->leftJoin('organization', 'organization.id = order.vendor_id')
            ->leftJoin('currency', 'currency.id = order.currency_id')
            ->andWhere($whereParams)
            ->groupBy('order.vendor_id')->orderBy(['total_sum' => SORT_DESC]);

        // фильтр - время создания заказа
        if (isset($post['search']['date']['from']) && $post['search']['date']['from']) {
            $query->andWhere('order.created_at >= :date_from',
                [':date_from' => date('Y-m-d H:i:s', strtotime($post['search']['date']['from'] . ' 00:00:00'))]);
        }
        if (isset($post['search']['date']['to']) && $post['search']['date']['to']) {
            $query->andWhere('order.created_at <= :date_to',
                [':date_to' => date('Y-m-d H:i:s', strtotime($post['search']['date']['to'] . ' 23:59:59'))]);
        }
        // фильтр - менеджер
        if (isset($post['search']['employee_id']) && $post['search']['employee_id']) {
            $query->andWhere(['order.created_by_id' => $post['search']['employee_id']]);
        }

        // лимит выборки
        if ($limit) {
            $query->limit($limit);
        }

        $result = [];
        foreach ($query->all() as $data) {
            $data['total_sum'] = round($data['total_sum'], 2);
            $result[] = $data;
        }
        return $result;

    }

    /**
     * Ресторан: Статистика по товарам
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function clientGoods($post)
    {

        // настройка пагинации
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);
        // ограничение на собственные заказы
        $whereParams = ['order.client_id' => $this->user->organization->id];

        // фильтр - поставщик
        if (isset($post['search']['vendor_id'])) {
            $whereParams['order.vendor_id'] = $post['search']['vendor_id'];
        }
        // фильтр - статус заказа
        $whereParams['order.status'] = self::ORDER_STATUSES_WELL;
        if (isset($post['search']['order_status_id']) && is_array($post['search']['order_status_id'])) {
            $whereParams['order.status'] = $post['search']['order_status_id'];
        }
        // фильтр - валюта
        if (isset($post['search']['currency_id'])) {
            $whereParams['currency.id'] = $post['search']['currency_id'];
        }

        // ТЕЛО ЗАПРОСА
        $query = new Query;
        $query->select(
            [
                'catalog_base_goods.product as name',
                'FORMAT(SUM(order_content.quantity), 2) AS count',
                'SUM(order_content.quantity * order_content.price) AS total_sum',
                'order.currency_id AS currency_id',
                'currency.symbol AS currency',
            ]
        )->from('order_content')
            ->leftJoin('catalog_base_goods', 'catalog_base_goods.id = order_content.product_id')
            ->leftJoin('order', 'order.id = order_content.order_id')
            ->leftJoin('currency', 'currency.id = order.currency_id')
            ->andWhere($whereParams)
            ->groupBy('order_content.product_id')->orderBy(['total_sum' => SORT_DESC]);

        // фильтр - время создания заказа
        if (isset($post['search']['date']['from']) && $post['search']['date']['from']) {
            $query->andWhere('order.created_at >= :date_from',
                [':date_from' => date('Y-m-d H:i:s', strtotime($post['search']['date']['from'] . ' 00:00:00'))]);
        }
        if (isset($post['search']['date']['to']) && $post['search']['date']['to']) {
            $query->andWhere('order.created_at <= :date_to',
                [':date_to' => date('Y-m-d H:i:s', strtotime($post['search']['date']['to'] . ' 23:59:59'))]);
        }
        // фильтр - менеджер
        if (isset($post['search']['employee_id']) && $post['search']['employee_id']) {
            $query->andWhere(['order.created_by_id' => $post['search']['employee_id']]);
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query->all()
        ]);
        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ((array)$dataProvider->models as $data) {
            $data['total_sum'] = round($data['total_sum'], 2);
            $result[] = $data;
        }
        return [
            'result' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

    }

    /**
     * Ресторан: Объем закупок за период
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function clientPurchases($post)
    {

        // ограничение на собственные заказы
        $whereParams = ['order.client_id' => $this->user->organization->id];

        // фильтр - поставщик
        if (isset($post['search']['vendor_id'])) {
            $whereParams['order.vendor_id'] = $post['search']['vendor_id'];
        }
        // фильтр - статус заказа
        $whereParams['order.status'] = self::ORDER_STATUSES_WELL;
        if (isset($post['search']['order_status_id']) && is_array($post['search']['order_status_id'])) {
            $whereParams['order.status'] = $post['search']['order_status_id'];
        }
        // фильтр - валюта
        if (isset($post['search']['currency_id'])) {
            $whereParams['currency.id'] = $post['search']['currency_id'];
        }

        // ТЕЛО ЗАПРОСА
        $query = new Query;
        $query->select(
            [
                'SUM(order_content.quantity * order_content.price) AS total_sum',
                'DATE_FORMAT(order.created_at, "%d.%m.%Y") AS date',
            ]
        )->from('order_content')
            ->leftJoin('order', 'order.id = order_content.order_id')
            ->leftJoin('currency', 'currency.id = order.currency_id')
            ->andWhere($whereParams)
            ->groupBy('date')->orderBy(['date' => SORT_ASC]);

        // фильтр - время создания заказа
        if (isset($post['search']['date']['from']) && $post['search']['date']['from']) {
            $query->andWhere('order.created_at >= :date_from',
                [':date_from' => date('Y-m-d H:i:s', strtotime($post['search']['date']['from'] . ' 00:00:00'))]);
        } else {
            $query->andWhere('order.created_at >= :date_from',
                [':date_from' => date('Y-m-d H:i:s', strtotime(date('Y-m-01') . ' 00:00:00'))]);
        }
        if (isset($post['search']['date']['to']) && $post['search']['date']['to']) {
            $query->andWhere('order.created_at <= :date_to',
                [':date_to' => date('Y-m-d H:i:s', strtotime($post['search']['date']['to'] . ' 23:59:59'))]);
        }
        // фильтр - менеджер
        if (isset($post['search']['employee_id']) && $post['search']['employee_id']) {
            $query->andWhere(['order.created_by_id' => $post['search']['employee_id']]);
        }

        $result = [];
        foreach ($query->all() as $data) {
            $data['total_sum'] = round($data['total_sum'], 2);
            $result[] = $data;
        }
        return [
            'result' => $result,
        ];

    }

    /**
     * Ресторан: Заказы по поставщикам
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function clientOrders($post)
    {
        return [
            'result' => $this->vendorTurnover($post, 15),
        ];
    }

    /**
     * Ресторан: Объем по поставщикам
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function clientVendors($post)
    {
        $total = 0;
        $result = $this->vendorTurnover($post, 15);
        foreach ($result as $row) {
            $total += $row['total_sum'];
        }
        foreach ($result as $k => $v) {
            $result[$k]['percent_sum'] = round(($v['total_sum'] / $total) * 100, 2);
        }
        return [
            'result' => $result,
        ];
    }

}