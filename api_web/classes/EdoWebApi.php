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

}