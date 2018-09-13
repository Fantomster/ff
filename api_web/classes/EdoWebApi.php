<?php

namespace api_web\classes;

use common\models\EdiOrganization;
use common\models\Organization;
use api_web\components\WebApi;
use common\components\EComIntegration;
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
     * @throws BadRequestHttpException
     * @return bool
     */
    public function acceptProducts(array $post): bool
    {

        if (!isset($post['order_id'])) {
            throw new BadRequestHttpException("empty_param|order_id");
        }

        $order = Order::findOne([
            'id' => $post['order_id'],
            'client_id' => $this->user->organization->id,
        ]);

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        } elseif ($order->service_id != (AllService::findOne(['denom' => 'EDI']))->id) {
            throw new BadRequestHttpException("Доступно только для документов ЭДО");
        } elseif ($order->status != OrderStatus::STATUS_EDO_SENT_BY_VENDOR) {
            throw new BadRequestHttpException("Должен быть статус \"Отправлено поставщиком\"");
        }

        $eComAccess = EdiOrganization::findOne(['organization_id' => $order->client_id]);
        if (!$eComAccess || !$eComAccess instanceof EdiOrganization) {
            throw new BadRequestHttpException("Отсутствуют параметры доступа к EDI");
        }

        if ((new EComIntegration())->sendOrderInfo($order, Organization::findOne($order->vendor_id),
            Organization::findOne($order->client_id), $eComAccess->login, $eComAccess->pass, true)) {
            $order->status = OrderStatus::STATUS_EDO_ACCEPTANCE_FINISHED;
            $order->save();
            return true;
        }

        throw new BadRequestHttpException("В процессе отправки данных возникла ошибка");

    }

    /**
     * История заказов
     * @param array $post
     * @return array
     */
    public function getOrderHistory(array $post)
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
    public function getOrderInfo(array $post)
    {

        if (!isset($post['order_id'])) {
            throw new BadRequestHttpException("empty_param|order_id");
        }

        $order = Order::findOne([
            'id' => $post['order_id'],
            'client_id' => $this->user->organization->id,
        ]);

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        } elseif ($order->service_id != (AllService::findOne(['denom' => 'EDI']))->id) {
            throw new BadRequestHttpException("Доступно только для документов ЭДО");
        }

        $res = $this->container->get('OrderWebApi')->getInfo($post);

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