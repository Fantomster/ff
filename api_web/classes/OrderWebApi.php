<?php

namespace api_web\classes;

use api_web\components\{
    ExcelRenderer, Registry, WebApiController
};
use api_web\components\notice_class\OrderNotice;
use api_web\helpers\{
    Product, WebApiHelper
};
use api_web\models\User;
use common\models\{
    CatalogBaseGoods,
    Delivery,
    MpCategory,
    OrderContent,
    OrderStatus,
    RelationSuppRest,
    Role,
    Order,
    Organization
};
use common\models\search\{
    OrderCatalogSearch, OrderContentSearch, OrderSearch
};
use api_web\components\Notice;
use kartik\mpdf\Pdf;
use yii\base\InvalidArgumentException;
use yii\data\{
    Pagination, SqlDataProvider
};
use yii\db\{
    Expression, Query
};
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use api_web\exceptions\ValidationException;

/**
 * Class OrderWebApi
 *
 * @package api_web\classes
 */
class OrderWebApi extends \api_web\components\WebApi
{
    /**
     * Редактирование заказа
     *
     * @param      $post
     * @param bool $isUnconfirmedVendor
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception|\Throwable
     */
    public function update($post, bool $isUnconfirmedVendor = false)
    {
        $this->validateRequest($post, ['order_id']);
        //Поиск заказа
        $order = Order::findOne($post['order_id']);

        if (!$order) {
            throw new BadRequestHttpException('order_not_found');
        }

        //If user is unconfirmed
        if ($isUnconfirmedVendor) {
            $organizationID = $this->user->organization_id;
            if ($this->checkUnconfirmedVendorAccess($post['order_id'], $organizationID, $this->user->status)) {
                //Задать стоимость доставки у вендора
                if (!empty($post['delivery_price'])) {
                    $delivery = Delivery::findOne(['vendor_id' => $organizationID]);
                    if (!$delivery) {
                        $delivery = new Delivery();
                        $delivery->vendor_id = $organizationID;
                    }
                    $delivery->delivery_charge = (float)$post['delivery_price'];
                    $delivery->save();
                }
            } else {
                throw new BadRequestHttpException("order.access.change.denied");
            }
        }
        if (!$this->accessAllow($order)) {
            throw new BadRequestHttpException("order.access.change.denied");
        }
        //OrderStatus::checkEdiOrderPermissions($order, 'edit');

        //Проверим статус заказа
        if (in_array($order->status, [OrderStatus::STATUS_CANCELLED, OrderStatus::STATUS_REJECTED])) {
            throw new BadRequestHttpException("order.access.change.canceled_status");
        }
        //Если сменили комментарий
        if (isset($post['comment']) && !$isUnconfirmedVendor) {
            $order->comment = trim($post['comment']);
        }
        //Если сменили дату доставки
        if (!empty($post['actual_delivery'])) {
            $order->actual_delivery = $post['actual_delivery'];
        }
        //Если поменяли скидку
        if (isset($post['discount']) && !empty($post['discount'])) {
            if (empty($post['discount']['type']) || !in_array(strtoupper($post['discount']['type']), ['FIXED', 'PERCENT'])) {
                throw new BadRequestHttpException("order.discount.types");
            }
            if (!isset($post['discount']['amount'])) {
                throw new BadRequestHttpException("order.discount.empty_amount");
            }
            $order->discount_type = strtoupper($post['discount']['type']) == 'FIXED' ? Order::DISCOUNT_FIXED : Order::DISCOUNT_PERCENT;

            if ($order->discount_type == Order::DISCOUNT_PERCENT && 100 < $post['discount']['amount']) {
                throw new BadRequestHttpException("order.discount.100_percent");
            }

            $order->discount = $post['discount']['amount'];
        }
        $tr = \Yii::$app->db->beginTransaction();
        if (is_null($order->created_by_id)) {
            $order->created_by_id = $this->user->id;
        }
        try {
            $changed = [];
            $deleted = [];
            //Тут операции с продуктами в этом заказе
            if (isset($post['products']) && !empty($post['products'])) {
                if (is_array($post['products'])) {
                    foreach ($post['products'] as $product) {
                        $operation = strtolower($product['operation']);
                        if (empty($operation) or !in_array($operation, ['delete', 'edit', 'add'])) {
                            throw new BadRequestHttpException("error.request");
                        }
                        switch ($operation) {
                            case 'delete':
                                $deleted[] = $this->deleteProduct($order, $product['id']);
                                break;
                            case 'add':
                                $change = $this->addProduct($order, $product);
                                $change->setIsNewRecord(true);
                                $changed[] = $change;
                                break;
                            case 'edit':
                                $changed[] = $this->editProduct($order, $product);
                                break;
                        }
                    }
                    if (isset($post['discount']['amount'])) {
                        if ($order->discount_type == Order::DISCOUNT_FIXED && $order->getTotalPriceWithOutDiscount() < $post['discount']['amount']) {
                            throw new BadRequestHttpException("order.discount.big_amount");
                        }
                    }

                    if ($order->positionCount == 0) {
                        switch ($this->user->organization->type_id) {
                            case Organization::TYPE_SUPPLIER:
                                $order->status = OrderStatus::STATUS_REJECTED;
                                break;
                            case Organization::TYPE_RESTAURANT:
                                $order->status = OrderStatus::STATUS_CANCELLED;
                                break;
                        }
                    }
                } else {
                    throw new BadRequestHttpException("error.request");
                }
            }

            $order->calculateTotalPrice();

            if (!$order->save()) {
                throw new ValidationException($order->getFirstErrors());
            }
            $tr->commit();
            $sender = $order->client;
            if ($order->vendor_id == $this->user->organization_id || $isUnconfirmedVendor) {
                $sender = $order->vendor;
            }
            if (!empty($changed) || !empty($deleted)) {
                Notice::init('Order')->sendOrderChange($sender, $order, $changed, $deleted);
            }
            return $this->getOrderInfo($order);
        } catch (\Throwable $e) {
            $tr->rollBack();
            throw $e;
        }
    }

    /**
     * Редактирвание продукта в заказе
     *
     * @param Order $order
     * @param array $product
     * @return OrderContent
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function editProduct(Order $order, array $product)
    {
        $this->validateRequest($product, ['id']);
        /**
         * @var OrderContent $orderContent
         */
        $orderContent = $order->getOrderContent()->where(['product_id' => $product['id']])->one();
        if (empty($orderContent)) {
            throw new BadRequestHttpException("order_content.not_found");
        }
        $oldOrderContentAttributes = $orderContent->attributes;

        if (!empty($product['quantity'])) {
            $orderContent->quantity = $product['quantity'];
        }

        $orderContent->comment = $product['comment'] ?? '';

        if (!empty($product['price']) || $product['price'] == 0) {
            $orderContent->price = round($product['price'], 3);
        }

        if ($orderContent->save()) {
            $orderContent->setOldAttributes($oldOrderContentAttributes);
            return $orderContent;
        } else {
            throw new ValidationException($orderContent->getFirstErrors());
        }
    }

    /**
     * Удаление продукта из заказа
     *
     * @param Order $order
     * @param int   $id
     * @return string
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function deleteProduct(Order $order, int $id)
    {
        if (empty($id)) {
            throw new BadRequestHttpException("order.delete_product_empty");
        }

        /** @var OrderContent $orderContentRow */
        $orderContentRow = $order->getOrderContent()->where(['product_id' => $id])->one();
        if (empty($orderContentRow)) {
            throw new BadRequestHttpException("order_content.not_found");
        }
        $model = $orderContentRow;
        $orderContentRow->delete();
        return $model;
    }

    /**
     * Добавление продукта в заказ
     *
     * @param Order $order
     * @param array $product
     * @return OrderContent
     * @throws BadRequestHttpException
     * @throws ValidationException|InvalidArgumentException
     */
    private function addProduct(Order $order, array $product)
    {
        $this->validateRequest($product, ['id']);

        $orderContentRow = $order->getOrderContent()->where(['product_id' => $product['id']])->one();

        if (empty($orderContentRow)) {
            $productModel = (new Product())->findFromCatalogs($product['id'], $this->user->organization->getCatalogs());
            if (!in_array($productModel['supp_org_id'], [$order->vendor->id])) {
                throw new BadRequestHttpException("order.bad_vendor|" . $order->vendor->name);
            }

            $orderContent = new OrderContent();
            $orderContent->order_id = $order->id;
            $orderContent->product_id = $productModel['id'];
            $orderContent->quantity = (new CartWebApi())->recalculationQuantity($productModel, $product['quantity'] ?? 1);
            $orderContent->comment = $product['comment'] ?? '';
            $orderContent->price = $product['price'] ?? $productModel['price'];
            $orderContent->initial_quantity = $orderContent->quantity;
            $orderContent->product_name = $productModel['product'];
            $orderContent->units = $productModel['units'];
            $orderContent->article = $productModel['article'];
            if ($orderContent->validate() && $orderContent->save()) {
                return $orderContent;
            } else {
                throw new ValidationException($orderContent->getFirstErrors());
            }
        } else {
            throw new BadRequestHttpException("order.add_product_is_already_in_order|" . $product['id']);
        }
    }

    /**
     * Оставляем комментарий к заказу
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException|InvalidArgumentException
     */
    public function addComment(array $post)
    {
        $this->validateRequest($post, ['order_id']);

        $order = Order::findOne($post['order_id']);

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        }

        if (!$this->accessAllow($order)) {
            throw new BadRequestHttpException("order.edit_comment_access_denied");
        }
        OrderStatus::checkEdiOrderPermissions($order, 'edit', [OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR]);

        $order->comment = $post['comment'];

        if (!$order->validate()) {
            throw new ValidationException($order->getFirstErrors());
        }

        $order->save();

        return ['order_id' => $order->id, 'comment' => $order->comment];
    }

    /**
     * Комментарий к конкретному товару в заказе
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \yii\base\InvalidArgumentException
     */
    public function addProductComment(array $post)
    {
        $this->validateRequest($post, ['order_id', 'product_id']);

        $order = Order::findOne($post['order_id']);

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        }

        if (!$this->accessAllow($order)) {
            throw new BadRequestHttpException("order.edit_comment_access_denied");
        }
        OrderStatus::checkEdiOrderPermissions($order, 'edit', [OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR]);

        $orderContent = OrderContent::findOne(['order_id' => $order->id, 'product_id' => (int)$post['product_id']]);

        if (empty($orderContent)) {
            throw new BadRequestHttpException("order_content.not_found");
        }

        $orderContent->comment = $post['comment'];

        if (!$orderContent->validate()) {
            throw new ValidationException($orderContent->getFirstErrors());
        }

        $orderContent->save();

        return [
            'order_id'   => $order->id,
            'product_id' => $orderContent->product_id,
            'comment'    => $orderContent->comment
        ];
    }

    /**
     * Информация о заказе
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getInfo(array $post)
    {
        $this->validateRequest($post, ['order_id']);

        /**@var Order $order */
        $order = Order::find()->where(['id' => $post['order_id'], 'service_id' => Registry::MC_BACKEND])->one();

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        }

        if (!$this->accessAllow($order)) {
            throw new BadRequestHttpException("order.view_access_denied");
        }

        return $this->getOrderInfo($order);
    }

    /**
     * @param Order $order
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     * @throws \Exception
     */
    public function getOrderInfo(Order $order)
    {
        $result = $order->attributes;
        $currency = $order->currency->symbol ?? "RUB";
        $currency_id = $order->currency->id;
        unset($result['updated_at'], $result['status'], $result['accepted_by_id'], $result['created_by_id'],
            $result['vendor_id'], $result['client_id'], $result['currency_id'], $result['discount_type']);
        $result['currency'] = $currency;
        $result['currency_id'] = $currency_id;
        $result['total_price'] = round($order->total_price, 2);
        $result['discount'] = round($order->discount, 2);
        $result['discount_type'] = null;

        if ($order->discount_type == Order::DISCOUNT_FIXED) {
            $result['discount_type'] = 'FIXED';
        }

        if ($order->discount_type == Order::DISCOUNT_PERCENT) {
            $result['discount_type'] = 'PERCENT';
        }

        $result['discount_type_id'] = $order->discount_type ?? null;

        $result['status_id'] = $order->status;
        $result['status_text'] = $order->statusText;
        $result['position_count'] = (int)$order->positionCount;
        $result['delivery_price'] = round($order->calculateDelivery(), 2);
        $result['min_order_price'] = round($order->forMinOrderPrice(), 2);
        $result['total_price_without_discount'] = round($order->getTotalPriceWithOutDiscount(), 2);
        $result['create_user'] = $order->createdByProfile->full_name ?? '';
        $result['accept_user'] = $order->acceptedByProfile->full_name ?? '';

        $result['items'] = [];

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = (int)$order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;
        $products = $dataProvider->models;

        $arEdiNumbers = [];
        if (!empty($products)) {
            foreach ($products as $model) {
                /**
                 * @var OrderContent $model
                 */
                $result['items'][] = $this->prepareProduct($model, $currency, $currency_id);
                if (!is_null($model->edi_number)) {
                    $arEdiNumbers[] = $model->edi_number;
                }
            }
        }
        $result['edi_number'] = array_unique($arEdiNumbers);

        $result['client'] = WebApiHelper::prepareOrganization($order->client);
        $result['vendor'] = WebApiHelper::prepareOrganization($order->vendor);

        if (!is_null($order->status_updated_at) && $order->status_updated_at != '0000-00-00 00:00:00') {
            $obUpdatedAt = WebApiHelper::asDatetime(trim($order->status_updated_at));
        } else {
            $obUpdatedAt = WebApiHelper::asDatetime();
        }

        if (!is_null($order->edi_doc_date) && $order->edi_doc_date != '0000-00-00 00:00:00') {
            $ediDocDate = WebApiHelper::asDatetime(trim($order->edi_doc_date));
        } else {
            $ediDocDate = WebApiHelper::asDatetime();
        }
        $result['status_updated_at'] = $obUpdatedAt;
        $result['edi_doc_date'] = $ediDocDate;

        return $result;
    }

    /**
     * История заказов
     *
     * @param array $post
     * @throws \Exception
     * @return array
     */
    public function getHistory(array $post)
    {
        $sort_field = (!empty($post['sort']) ? $post['sort'] : null);
        $page = (!empty($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (!empty($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $search = new OrderSearch();

        WebApiHelper::clearRequest($post);

        if (!isset($post['search']['service_id'])) {
            $search->service_id = Registry::MC_BACKEND;
        }

        if (isset($post['search'])) {

            if (isset($post['search']['id']) && !empty($post['search']['id'])) {
                $search->id = $post['search']['id'];
            }

            if (isset($post['search']['service_id']) && !empty($post['search']['service_id'])) {
                $search->service_id = $post['search']['service_id'];
            } else {
                $search->service_id_excluded = Registry::$edo_documents;
            }

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
        $models = $dataProvider->models;
        if (!empty($models)) {
            /**
             * @var Order $model
             */
            foreach ($models as $model) {
                if ($model->status == OrderStatus::STATUS_DONE) {
                    $date = $model->completion_date ?? $model->actual_delivery;
                } else {
                    $date = $model->updated_at;
                }

                if (!is_null($model->status_updated_at) && $model->status_updated_at != '0000-00-00 00:00:00') {
                    $obUpdatedAt = WebApiHelper::asDatetime(trim($model->status_updated_at));
                } else {
                    $obUpdatedAt = WebApiHelper::asDatetime();
                }

                if (!is_null($model->edi_doc_date) && $model->edi_doc_date != '0000-00-00 00:00:00') {
                    $ediDocDate = WebApiHelper::asDatetime(trim($model->edi_doc_date));
                } else {
                    $ediDocDate = WebApiHelper::asDatetime();
                }

                $orderInfo = [
                    'id'                => (int)$model->id,
                    'created_at'        => WebApiHelper::asDatetime($model->created_at),
                    'status_updated_at' => $obUpdatedAt,
                    'edi_doc_date'      => $ediDocDate,
                    'completion_date'   => isset($date) ? WebApiHelper::asDatetime($date) : null,
                    'status'            => (int)$model->status,
                    'status_text'       => $model->statusText,
                    'vendor'            => $model->vendor->name,
                    'currency_id'       => $model->currency_id,
                    'create_user'       => $model->createdByProfile->full_name ?? '',
                    'accept_user'       => $model->acceptedByProfile->full_name ?? '',
                    'count_position'    => count($model->orderContent),
                    'total_price'       => round($model->total_price, 2) ?? 0,
                    'edi_number'        => $model->ediNumber
                ];
                $orders[] = $orderInfo;
            }
        }

        $return = [
            'orders'     => $orders,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ],
            'sort'       => $sort_field
        ];

        return $return;
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
            ->andWhere(
                ['OR',
                    ['not in', 'service_id', Registry::$edo_documents],
                    ['service_id' => null]
                ]
            )
            ->groupBy('status')
            ->all();

        $return = [
            'waiting'    => 0,
            'processing' => 0,
            'success'    => 0,
            'canceled'   => 0
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

    /**
     * @param      $post
     * @param bool $isUnconfirmedVendor
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function products($post, bool $isUnconfirmedVendor = false)
    {
        $sort = (isset($post['sort']) ? $post['sort'] : null);
        $searchString = (isset($post['search']['product']) ? $post['search']['product'] : null);
        if ($isUnconfirmedVendor) {
            if (empty($post['search']['order_id'])) {
                throw new BadRequestHttpException('empty_param|search.order_id');
            }
            $order = Order::findOne(['id' => (int)$post['search']['order_id']]);
            if (empty($order)) {
                throw new BadRequestHttpException('order_not_found');
            }
            $organizationID = $this->user->organization_id;
            if ($this->checkUnconfirmedVendorAccess($order->id, $organizationID, $this->user->status)) {
                $searchSupplier = $organizationID;
                $client = Organization::findOne(['id' => $order->client_id]);
                $vendors = [$organizationID];
                $catalogs = $vendors ? $client->getCatalogs(null) : false;
            } else {
                throw new BadRequestHttpException("order.edit_access_denied");
            }
        } else {
            $searchSupplier = $post['search']['supplier_id'] ?? null;
            $client = $this->user->organization;
            $vendors = $client->getSuppliers('', false);
            $catalogs = $vendors ? $client->getCatalogs(null) : false;
        }

        $searchCategory = $post['search']['category_id'] ?? null;
        $searchPrice = (isset($post['search']['price']) ? $post['search']['price'] : null);
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        //Готовим ответ
        $return = [
            'headers'    => [],
            'products'   => [],
            'pagination' => [
                'page'       => $page,
                'page_size'  => $pageSize,
                'total_page' => 0,
            ]
        ];

        if ($catalogs) {
            $searchModel = new OrderCatalogSearch();

            $searchModel->client = $client;
            $searchModel->catalogs = $catalogs;

            /**
             * @var SqlDataProvider $dataProvider
             */
            $searchModel->searchString = $searchString;
            $searchModel->selectedVendor = $searchSupplier;
            $searchModel->searchCategory = $searchCategory;
            $searchModel->searchPrice = $searchPrice;
            $dataProvider = $searchModel->search(['page' => $page, 'pageSize' => $pageSize]);
            $return['pagination']['total_page'] = ceil($dataProvider->totalCount / $pageSize);

            //Результат
            if ($sort) {
                $order = (preg_match('#^-(.+?)$#', $sort) ? SORT_DESC : SORT_ASC);

                $field = str_replace('-', '', $sort);

                if ($field == 'supplier' || $field == 'supplier_id') {
                    $field = 'name';
                }

                $dataProvider->sort->defaultOrder = [$field => $order];
                $return['sort'] = $sort;
            }

            $result = $dataProvider->getModels();
            foreach ($result as $model) {
                $return['products'][] = [
                    'id'          => (int)$model['id'],
                    'product_id'  => (int)$model['id'],
                    'product'     => $model['product'],
                    'article'     => $model['article'],
                    'supplier'    => $model['name'],
                    'supp_org_id' => (int)$model['supp_org_id'],
                    'cat_id'      => (int)$model['cat_id'],
                    'category_id' => (int)$model['category_id'],
                    'price'       => round($model['price'], 2),
                    'ed'          => $model['ed'],
                    'units'       => round(($model['units'] ?? 0), 3),
                    'currency'    => $model['symbol'],
                    'currency_id' => (int)$model['currency_id'],
                    'image'       => @$this->container->get('MarketWebApi')->getProductImage(CatalogBaseGoods::findOne($model['id'])),
                    'in_basket'   => $this->container->get('CartWebApi')->countProductInCart($model['id']),
                    'edi_product' => $model['edi_supplier_article'] > 0 ? true : false,
                ];
            }

            /**
             * @var CatalogBaseGoods $model
             */
            if (isset($return['products'][0])) {
                foreach (array_keys($return['products'][0]) as $key) {
                    $return['headers'][$key] = (new CatalogBaseGoods())->getAttributeLabel($key);
                }
            }
        }

        return $return;
    }

    /**
     * Список доступных категорий
     *
     * @param null $post
     * @param bool $isUnconfirmedVendor
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function categories($post = null, bool $isUnconfirmedVendor = false)
    {
        $organizationID = $this->user->organization_id;
        if ($isUnconfirmedVendor) {
            $this->validateRequest($post, ['order_id']);

            $order = Order::findOne(['id' => $post['order_id']]);
            if ($this->checkUnconfirmedVendorAccess($post['order_id'], $organizationID, $this->user->status)) {
                $suppliers = [$this->user->organization_id];
                $organizationID = $order->client_id;
            } else {
                throw new BadRequestHttpException("order.edit_access_denied");
            }
        } else {
            $suppliers = RelationSuppRest::find()
                ->select('supp_org_id')
                ->where(['rest_org_id' => $this->user->organization_id, 'status' => 1])
                ->column();
        }

        $query = (new Query())
            ->distinct()
            ->select([
                'COALESCE(category_id, 0) as category_id'
            ])
            ->from('catalog_base_goods')
            ->leftJoin('mp_category', 'mp_category.id = category_id')
            ->where(['in', 'supp_org_id', $suppliers])
            ->column();
        $return = [];
        foreach ($query as $id) {

            if ($id == 0) {
                $return[9999] = [
                    'id'            => (int)$id,
                    'name'          => 'Без категории',
                    'count_product' => MpCategory::getProductCountWithOutCategory(null, $organizationID)
                ];
                continue;
            }

            $model = MpCategory::findOne($id);
            if (!empty($model->parent)) {
                if (!isset($return[$model->parentCategory->id])) {
                    $return[$model->parentCategory->id] = [
                        'id'    => $model->parentCategory->id,
                        'name'  => $model->parentCategory->name,
                        'image' => $this->container->get('MarketWebApi')->getCategoryImage($model->parentCategory->id)
                    ];
                }
                $return[$model->parentCategory->id]['subcategories'][] = [
                    'id'            => $model->id,
                    'name'          => $model->name,
                    'image'         => $this->container->get('MarketWebApi')->getCategoryImage($model->id),
                    'count_product' => $model->getProductCount(null, $this->user->organization_id),
                ];
            } else {
                if (!isset($return[$model->id])) {
                    $return[$model->id] = [
                        'id'    => $model->id,
                        'name'  => $model->name,
                        'image' => $this->container->get('MarketWebApi')->getCategoryImage($model->id)
                    ];
                }
            }
        }

        //Сортируем по ключу
        ksort($return);
        //Убиваем ключи сортировки
        $return = array_values($return);

        return $return;
    }

    /**
     * Отмена заказа
     *
     * @param array $post
     * @param bool  $isUnconfirmedVendor
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function cancel(array $post, bool $isUnconfirmedVendor = false)
    {
        $this->validateRequest($post, ['order_id']);
        //todo_refactoring with $this->complete() method duplicate rows
        $query = Order::find()->where(['id' => $post['order_id']]);
        if ($this->user->organization->type_id == Organization::TYPE_RESTAURANT) {
            $query->andWhere(['client_id' => $this->user->organization->id]);
        } else {
            $query->andWhere(['vendor_id' => $this->user->organization->id]);
        }
        /**@var Order $order */
        $order = $query->one();

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        }

        if ($order->status == OrderStatus::STATUS_DONE) {
            throw new BadRequestHttpException("order.already_done");
        }

        if ($order->status == OrderStatus::STATUS_CANCELLED) {
            throw new BadRequestHttpException("order.already_cancel");
        }
        OrderStatus::checkEdiOrderPermissions($order, 'cancel');

        $t = \Yii::$app->db->beginTransaction();
        try {

            if ($isUnconfirmedVendor) {
                $organizationID = $this->user->organization_id;
                if ($this->checkUnconfirmedVendorAccess($post['order_id'], $organizationID, $this->user->status)) {
                    $order->status = OrderStatus::STATUS_REJECTED;
                } else {
                    throw new BadRequestHttpException("order.edit_access_denied");
                }
            } else {
                $order->status = OrderStatus::STATUS_CANCELLED;
            }

            if (!$order->validate() || !$order->save()) {
                throw new ValidationException($order->getFirstErrors());
            }

            if ($this->user->organization->type_id == Organization::TYPE_RESTAURANT) {
                $organization = $order->client;
            } else {
                $organization = $order->vendor;
            }

            $t->commit();
            Notice::init('Order')->cancelOrder($this->user, $organization, $order);

            return $this->getInfo(['order_id' => $order->id]);
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * Повторить заказ
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function repeat(array $post)
    {
        $this->validateRequest($post, ['order_id']);

        $order = Order::findOne(['id' => $post['order_id'], 'client_id' => $this->user->organization->id]);

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $content = $order->orderContent;
            if (empty($content)) {
                throw new BadRequestHttpException("order_content.empty");
            }

            $request = [];
            foreach ($content as $item) {
                $request[] = $this->prepareProduct($item);
            }
            //Добавляем товары для заказа в корзину
            $this->container->get('CartWebApi')->add($request);
            $t->commit();
            return $this->container->get('CartWebApi')->items();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * Заверщить заказ
     *
     * @param array $post
     * @param bool  $isUnconfirmedVendor
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function complete(array $post, bool $isUnconfirmedVendor = false)
    {
        $this->validateRequest($post, ['order_id']);

        $vendor = false;
        $query = Order::find()->where(['id' => $post['order_id']]);
        if ($this->user->organization->type_id == Organization::TYPE_RESTAURANT) {
            $query->andWhere(['client_id' => $this->user->organization->id]);
        } else {
            $vendor = true;
            $query->andWhere(['vendor_id' => $this->user->organization->id]);
        }
        /**@var Order $order */
        $order = $query->one();

        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        }

        if ($order->status == OrderStatus::STATUS_DONE) {
            throw new BadRequestHttpException("order.already_done");
        }
        OrderStatus::checkEdiOrderPermissions($order, 'complete');

        $t = \Yii::$app->db->beginTransaction();
        try {
            $order->status = ($vendor ? Order::STATUS_PROCESSING : OrderStatus::STATUS_DONE);
            $order->actual_delivery = gmdate("Y-m-d H:i:s");
            $order->completion_date = new Expression('NOW()');
            if (!$order->save()) {
                throw new ValidationException($order->getFirstErrors());
            }
            $t->commit();
            $sender = $order->client;
            if ($order->vendor_id == $this->user->organization_id || $isUnconfirmedVendor) {
                $sender = $order->vendor;
            }
            /** @var OrderNotice $notice */
            $notice = Notice::init('Order');
            if ($order->status == Order::STATUS_PROCESSING) {
                $notice->processingOrder($order, $this->user, $sender);
            } elseif ($order->status == Order::STATUS_DONE) {
                $notice->doneOrder($order, $this->user, $sender);
            }

            return $this->getInfo(['order_id' => $order->id]);
        } catch (\Throwable $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * Сохранение заказа в PDF
     *
     * @param array            $post
     * @param WebApiController $c
     * @return false|string
     * @throws BadRequestHttpException|InvalidArgumentException
     */
    public function saveToPdf(array $post, WebApiController $c)
    {
        $this->validateRequest($post, ['order_id']);

        $order = Order::findOne(['id' => $post['order_id']]);
        if (empty($order)) {
            throw new BadRequestHttpException('order_not_found');
        }

        $user = $this->user;

        if ((($order->client_id != $user->organization_id) && ($order->vendor_id != $user->organization_id) && ($order->created_by_id != $user->id))) {
            throw new BadRequestHttpException(\Yii::t('message', 'frontend.controllers.order.get_out_three', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }

        $order->calculateTotalPrice();
        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        $pathView = \Yii::getAlias('@frontend') . '/views/order/';
        $pdf = new Pdf([
            'mode'        => Pdf::MODE_UTF8,
            'format'      => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'content'     => $c->renderFile($pathView . '_pdf_order.php', compact('dataProvider', 'order')),
            'options'     => [
                'defaultfooterline'      => false,
                'defaultfooterfontstyle' => false,
            ],
            'methods'     => [
                'SetFooter' => $c->renderFile($pathView . '_pdf_signature.php'),
            ],
            'cssFile'     => \Yii::getAlias('@frontend') . '/web/css/pdf_styles.css'
        ]);
        $pdf->filename = 'mixcart_order_' . $post['order_id'] . '.pdf';
        ob_start();
        $pdf->render();
        $content = ob_get_clean();
        $base64 = (isset($post['base64_encode']) && $post['base64_encode'] == 1 ? true : false);
        return ($base64 ? base64_encode($content) : $content);
    }

    /**
     * Изменение номера документа
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function setDocumentNumber($post)
    {
        $this->validateRequest($post, ['order_id', 'document_number']);

        $model = Order::find()->where(['id' => $post['order_id'], 'client_id' => $this->user->organization_id])->one();

        if (empty($model)) {
            throw new BadRequestHttpException('order_not_found');
        }

        if (!in_array($model->service_id, [Registry::MC_BACKEND, Registry::VENDOR_DOC_MAIL_SERVICE_ID])) {
            throw new BadRequestHttpException(
                'bad_service_id_in_order|' .
                ($model->service_id ?? "NULL") . '|' .
                Registry::MC_BACKEND . ' or ' . Registry::VENDOR_DOC_MAIL_SERVICE_ID
            );
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $result = (bool)OrderContent::updateAll(
                ['edi_number' => trim($post['document_number'])],
                'order_id = :oid',
                [':oid' => $model->id]
            );
            $t->commit();
            return ['result' => $result];
        } catch (\Throwable $e) {
            $t->rollBack();
            \Yii::info($e->getMessage());
            return ['result' => false];
        }
    }

    /**
     * @param OrderContent $model
     * @param null         $currency
     * @param null         $currency_id
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function prepareProduct(OrderContent $model, $currency = null, $currency_id = null)
    {
        $quantity = !empty($model->quantity) ? round($model->quantity, 3) : round($model->product->units, 3);

        $item = [];
        $item['id'] = (int)$model->id;
        $item['product'] = $model->product->product;
        $item['product_id'] = $model->productFromCatalog->base_goods_id ?? $model->product->id;
        $item['catalog_id'] = $model->productFromCatalog->cat_id ?? $model->product->cat_id;
        $item['price'] = round($model->price, 2);
        $item['quantity'] = $quantity;
        $item['comment'] = $model->comment ?? '';
        $item['total'] = round($model->total, 2);
        $item['rating'] = round($model->product->ratingStars, 1);
        $item['brand'] = $model->product->brand ? $model->product->brand : '';
        $item['article'] = $model->product->article;
        $item['ed'] = $model->product->ed;
        $item['units'] = $model->product->units;
        $item['currency'] = $currency ?? $model->product->catalog->currency->symbol;
        $item['currency_id'] = $currency_id ?? (int)$model->product->catalog->currency->id;
        $item['image'] = $this->container->get('MarketWebApi')->getProductImage($model->product);
        $item['edi_number'] = $model->edi_number;
        $item['edi_product'] = $model->product->edi_supplier_article > 0 ? true : false;
        return $item;
    }

    /**
     * Доступ к изменению заказа
     *
     * @param $order
     * @return bool
     */
    private function accessAllow($order)
    {
        $user = $this->user;

        if ($order->client_id == $user->organization_id) {
            return true;
        }

        if ($order->vendor_id == $user->organization_id) {
            return true;
        }

        $roles = ArrayHelper::merge([
            Role::ROLE_RESTAURANT_MANAGER,
            Role::ROLE_RESTAURANT_EMPLOYEE,
            Role::ROLE_SUPPLIER_MANAGER,
            Role::ROLE_SUPPLIER_EMPLOYEE,
            Role::ROLE_FKEEPER_MANAGER,
            Role::ROLE_ADMIN
        ],
            Role::getFranchiseeEditorRoles()
        );

        if (in_array($user->role_id, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * Доступ к изменению заказа
     *
     * @param int $orderID
     * @param int $organizationID
     * @param     $status
     * @return bool
     */
    private function checkUnconfirmedVendorAccess(int $orderID, int $organizationID, $status): bool
    {
        $order = Order::findOne(['id' => $orderID]);
        $organization = Organization::findOne(['id' => $organizationID]);
        if ($organization->type_id == Organization::TYPE_SUPPLIER && $organizationID == $order->vendor_id && ($status == User::STATUS_UNCONFIRMED_EMAIL || $organization->is_work == 0)) {
            return true;
        }
        return false;
    }

    /**
     * Сохранение заказа в Excel
     *
     * @param array $post
     * @return false|string
     * @throws BadRequestHttpException|\Exception
     */
    public function saveToExcel(array $post)
    {
        $this->validateRequest($post, ['order_id']);

        $objPHPExcel = (new ExcelRenderer())->OrderRender($post['order_id']);
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save(tempnam("/tmp", "excel"));
        ob_start();
        $objWriter->save('php://output');
        $content = ob_get_clean();
        $base64 = (isset($post['base64_encode']) && $post['base64_encode'] == 1 ? true : false);
        return ($base64 ? base64_encode($content) : $content);
    }

}