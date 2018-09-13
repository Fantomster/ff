<?php

namespace api_web\classes;

use api_web\components\WebApi;
use common\models\AllService;
use common\models\Order;
use common\models\OrderStatus;
use yii\db\Query;
use yii\web\BadRequestHttpException;

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
        $post['search']['service_id'] = (AllService::findOne(['denom' => 'EDI']))->id;
        return $this->container->get('OrderWebApi')->getHistory($post);
    }

    /**
     * Карточка заказа
     * @param array $post
     * @throws BadRequestHttpException
     * @return array
     */
    public function orderInfo(array $post)
    {

        if (!isset($post['search']['order_id'])) {
            throw new BadRequestHttpException("empty_param|order_id");
        }

        $order = Order::findOne([
            'id' => $post['search']['order_id'],
            'client_id' => $this->user->organization->id,
        ]);

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        } elseif ($order->service_id != (AllService::findOne(['denom' => 'EDI']))->id) {
            throw new BadRequestHttpException("Доступно только для документов ЭДО");
        }

        $res = $this->container->get('OrderWebApi')->getInfo($post['search']);

        if (isset($res['items']) && $res['items']) {
            foreach ($res['items'] as $k => $v) {

                $difference = null;

                $oldPrice = (new Query())->select(['order_content.price as price'])->from('order_content')
                    ->leftJoin('order', 'order.id = order_content.order_id')
                    ->andWhere([
                        'order_content.product_id' => $v['product_id'],
                        'order.client_id' => $this->user->organization->id,
                    ])
                    ->andWhere(['<', 'order.created_at', $order->created_at])
                    ->orderBy(['`order`.created_at' => SORT_DESC])
                    ->limit(1)->one();
                if (isset($oldPrice['price'])) {
                    $oldPrice = $oldPrice['price'];
                } else {
                    $oldPrice = 0;
                }
                if ($oldPrice) {
                    $priceChangeValue = ($v['price'] - $oldPrice);
                    $priceChangeDirection = 'up';
                    if ($priceChangeValue < 0) {
                        $priceChangeDirection = 'down';
                        $priceChangeValue = -1 * $priceChangeValue;
                    }
                    if ($priceChangeValue) {
                        $difference = [
                            'class' => $priceChangeDirection,
                            'price' => $oldPrice,
                        ];
                    }
                }
                $res['items'][$k]['difference'] = $difference;
            }
        }

        return [
            'action' => OrderStatus::getClientPermissions($order->status),
            'order' => $res,
        ];
    }

    /**
     * Количество заказов в разных статусах
     * @return array
     */
    public function getHistoryCount()
    {

        $result = (new Query())->from(Order::tableName())
            ->select(['status', 'COUNT(status) as count'])
            ->where([
                'or',
                ['client_id' => $this->user->organization->id],
                ['vendor_id' => $this->user->organization->id],
            ])
            ->andWhere(['service_id' => (AllService::findOne(['denom' => 'EDI']))->id])
            ->groupBy('status')
            ->all();

        $return = [
            'waiting' => 0,
            'processing' => 0,
            'sent_by_vendor' => 0,
            'acceptance_finished' => 0,
            'success' => 0,
            'canceled' => 0
        ];

        if (!empty($result)) {
            foreach ($result as $row) {
                switch ($row['status']) {
                    case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                    case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                        $return['waiting'] += $row['count'];
                        break;
                    case OrderStatus::STATUS_PROCESSING:
                        $return['processing'] += $row['count'];
                        break;
                    case OrderStatus::STATUS_EDO_SENT_BY_VENDOR:
                        $return['sent_by_vendor'] += $row['count'];
                        break;
                    case OrderStatus::STATUS_EDO_ACCEPTANCE_FINISHED:
                        $return['acceptance_finished'] += $row['count'];
                        break;
                    case OrderStatus::STATUS_DONE:
                        $return['success'] += $row['count'];
                        break;
                    case OrderStatus::STATUS_CANCELLED:
                    case OrderStatus::STATUS_REJECTED:
                        $return['canceled'] += $row['count'];
                        break;
                }
            }
        }

        return $return;
    }


}