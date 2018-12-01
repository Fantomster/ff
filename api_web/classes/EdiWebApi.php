<?php

namespace api_web\classes;

use api_web\components\Registry;
use common\components\edi\EDIIntegration;
use common\models\edi\EdiOrganization;
use api_web\components\WebApi;
use common\models\Order;
use common\models\OrderStatus;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * Class EdiWebApi
 *
 * @package   api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-11
 * @author    Mixcart
 * @module    WEB-API
 * @version   2.0
 */
class EdiWebApi extends WebApi
{

    /**
     * Завершение приемки товаров по заказу
     *
     * @param array $post
     * @throws BadRequestHttpException
     * @return array
     */
    public function acceptProducts(array $post): array
    {
        $this->validateRequest($post, ['order_id']);
        $order = Order::findOne([
            'id'        => $post['order_id'],
            'client_id' => $this->user->organization->id,
        ]);

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        }

        if (!in_array($order->service_id, [Registry::EDI_SERVICE_ID, Registry::VENDOR_DOC_MAIL_SERVICE_ID])) {
            throw new BadRequestHttpException("Доступно только для документов ЭДО и Накладных поставщика");
        }

        if ($order->status != OrderStatus::STATUS_EDI_SENT_BY_VENDOR) {
            throw new BadRequestHttpException("Должен быть статус \"Отправлено поставщиком\"");
        }

        if ($order->service_id == Registry::EDI_SERVICE_ID) {
            $eComAccess = EdiOrganization::findOne(['organization_id' => $order->client_id]);
            if (!$eComAccess) {
                throw new BadRequestHttpException("Отсутствуют параметры доступа к EDI");
            }
            $glnArray = $order->client->getGlnCodes($order->client->id, $order->vendor->id);
            $ediIntegration = new EDIIntegration(['orgId' => $order->vendor_id, 'clientId' => $order->client_id, 'providerID' => $glnArray['provider_id']]);
            if (!$ediIntegration) {
                throw new BadRequestHttpException("В процессе отправки данных возникла ошибка");
            }
        }

        if (is_null($order->created_by_id)) {
            $order->created_by_id = $this->user->id;
        }

        $order->status = OrderStatus::STATUS_EDI_ACCEPTANCE_FINISHED;
        $order->save();
        return ['result' => true];
    }

    /**
     * Завершение заказа
     *
     * @param array $post
     * @throws BadRequestHttpException
     * @return array
     */
    public function orderComplete(array $post): array
    {
        $this->validateRequest($post, ['order_id']);

        $order = Order::findOne([
            'id'        => $post['order_id'],
            'client_id' => $this->user->organization->id,
        ]);

        if (empty($order)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'order_not_found'));
        } elseif (!in_array($order->service_id, [Registry::EDI_SERVICE_ID, Registry::VENDOR_DOC_MAIL_SERVICE_ID])) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'order.available_for_edi_order'));
        } elseif ($order->status != OrderStatus::STATUS_EDI_ACCEPTANCE_FINISHED) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'order.status_must_be') . \Yii::t('app', 'common.models.order_status.status_edo_acceptance_finished'));
        }

        $order->status = OrderStatus::STATUS_DONE;
        $order->save();
        return ['result' => true];
    }

    /**
     * Отмена заказа
     *
     * @param array $post
     * @throws BadRequestHttpException
     * @return array
     */
    public function orderCancel(array $post): array
    {
        $this->validateRequest($post, ['order_id']);

        $order = Order::findOne([
            'id'        => $post['order_id'],
            'client_id' => $this->user->organization_id,
        ]);

        if (empty($order)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'order_not_found'));
        } elseif (!in_array($order->service_id, [Registry::EDI_SERVICE_ID, Registry::VENDOR_DOC_MAIL_SERVICE_ID])) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'order.available_for_edi_order'));
        } elseif ($order->status != OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'order.status_must_be') . \Yii::t('app', 'common.models.order_status.status_awaiting_accept_from_vendor'));
        }

        $order->status = OrderStatus::STATUS_CANCELLED;
        $order->save();

        return ['result' => true];
    }

    /**
     * История заказов
     *
     * @param array $post
     * @return array
     */
    public function getOrderHistory(array $post)
    {
        $post['search']['service_id'] = [Registry::EDI_SERVICE_ID, Registry::VENDOR_DOC_MAIL_SERVICE_ID];
        return $this->container->get('OrderWebApi')->getHistory($post);
    }

    /**
     * Карточка заказа
     *
     * @param array $post
     * @throws BadRequestHttpException|\Exception
     * @return array
     */
    public function getOrderInfo(array $post)
    {
        $this->validateRequest($post, ['order_id']);

        $order = Order::findOne([
            'id'        => $post['order_id'],
            'client_id' => $this->user->organization->id,
        ]);

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        } elseif (!in_array($order->service_id, [Registry::EDI_SERVICE_ID, Registry::VENDOR_DOC_MAIL_SERVICE_ID])) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'order.available_for_edi_order'));
        }

        $res = $this->container->get('OrderWebApi')->getInfo($post);

        if (isset($res['items']) && !empty($res['items'])) {
            $productIds = array_map(function ($el) {
                return $el['product_id'];
            }, $res['items']);

            $oldPrices = (new Query())->select(['oc.price as price', 'oc.product_id'])
                ->from('order_content oc')
                ->leftJoin('order', 'order.id = oc.order_id')
                ->andWhere([
                    'oc.product_id'   => $productIds,
                    'order.client_id' => $this->user->organization->id,
                ])
                ->andWhere(['<', 'order.created_at', $order->created_at])
                ->orderBy(['`order`.created_at' => SORT_DESC])
                ->indexBy('product_id')
                ->all();

            foreach ($res['items'] as $k => $v) {
                $difference = null;
                if (array_key_exists($v['product_id'], $oldPrices)) {
                    $oldPrice = $oldPrices[$v['product_id']];

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
                        }
                        if ($priceChangeValue) {
                            $difference = [
                                'trend_type'  => $priceChangeDirection,
                                'price'       => $oldPrice,
                                'priceChange' => round($priceChangeValue, 2),
                                'percent'     => round($v['price'] * 100 / $oldPrice - 100, 2),
                            ];
                        }
                    }
                }
                $res['items'][$k]['difference'] = $difference;
            }
        }

        return [
            'action' => OrderStatus::getClientPermissions($order->status),
            'order'  => $res,
        ];
    }

    /**
     * Количество заказов в разных статусах
     *
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
            ->andWhere(['service_id' => [
                Registry::EDI_SERVICE_ID,
                Registry::VENDOR_DOC_MAIL_SERVICE_ID
            ]])
            ->groupBy('status')
            ->all();

        $return = [
            'waiting'             => 0,
            'processing'          => 0,
            'sent_by_vendor'      => 0,
            'acceptance_finished' => 0,
            'success'             => 0,
            'canceled'            => 0
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
                    case OrderStatus::STATUS_EDI_SENT_BY_VENDOR:
                        $return['sent_by_vendor'] += $row['count'];
                        break;
                    case OrderStatus::STATUS_EDI_ACCEPTANCE_FINISHED:
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