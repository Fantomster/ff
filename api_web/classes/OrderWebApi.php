<?php

namespace api_web\classes;

use api_web\controllers\OrderController;
use api_web\helpers\Product;
use api_web\helpers\WebApiHelper;
use common\models\CatalogBaseGoods;
use common\models\MpCategory;
use common\models\OrderContent;
use common\models\RelationSuppRest;
use common\models\Role;
use common\models\search\OrderCatalogSearch;
use common\models\search\OrderContentSearch;
use common\models\search\OrderSearch;
use common\models\Order;
use common\models\Organization;
use api_web\components\Notice;
use kartik\mpdf\Pdf;
use yii\data\Pagination;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use api_web\exceptions\ValidationException;

/**
 * Class OrderWebApi
 * @package api_web\classes
 */
class OrderWebApi extends \api_web\components\WebApi
{
    /**
     * Регистрация заказа/ов с корзины
     * @param array $orders
     * @return array
     * @throws \Exception
     *
     * public function registration(array $orders)
     * {
     * $transaction = \Yii::$app->db->beginTransaction();
     * try {
     * $user = $this->user;
     * $client = $user->organization;
     *
     * if (empty($client->getCartCount())) {
     * throw new BadRequestHttpException("Корзина пуста.");
     * }
     *
     * if (empty($orders['orders'])) {
     * throw new BadRequestHttpException("Необходимо передать список заказов для оформления.");
     * }
     *
     * $return = [];
     * foreach ($orders['orders'] as $order_id) {
     * $order = Order::findOne([
     * 'id' => $order_id,
     * 'client_id' => $client->id,
     * 'status' => Order::STATUS_FORMING
     * ]);
     *
     * if (empty($order)) {
     * $return[] = [
     * 'order_id' => $order_id,
     * 'result' => 'Не найден'
     * ];
     * continue;
     * }
     *
     * $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
     * $order->created_by_id = $user->id;
     * $order->created_at = gmdate("Y-m-d H:i:s");
     *
     * if (!$order->validate()) {
     * throw new ValidationException($order->getFirstErrors());
     * }
     *
     * $return[] = [
     * 'order_id' => $order->id,
     * 'result' => $order->save()
     * ];
     *
     * //Сообщение в очередь поставщику, что есть новый заказ
     * Notice::init('Order')->sendOrderToTurnVendor($order->vendor);
     * //Емайл и смс о новом заказе
     * Notice::init('Order')->sendEmailAndSmsOrderCreated($client, $order);
     * }
     * //Сообщение в очередь, Изменение количества товара в корзине
     * Notice::init('Order')->sendOrderToTurnClient($client);
     * $transaction->commit();
     * return $return;
     * } catch (\Exception $e) {
     * $transaction->rollBack();
     * throw $e;
     * }
     * }
     */


    /**
     * Редактирование заказа
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function update($post)
    {
        WebApiHelper::clearRequest($post);
        if (empty($post['order_id'])) {
            throw new BadRequestHttpException('Empty order_id');
        }
        //Поиск заказа
        $order = Order::findOne($post['order_id']);
        if (!$this->accessAllow($order)) {
            throw new BadRequestHttpException("У вас нет прав на изменение заказа.");
        }
        //Проверим статус заказа
        if (in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_REJECTED])) {
            throw new BadRequestHttpException("Заказ в статусе 'Отменен' нельзя редактировать.");
        }
        //Если сменили комментарий
        if (!empty($post['comment'])) {
            $order->comment = trim($post['comment']);
        }
        //Если поменяли скидку
        if (!empty($post['discount'])) {
            if (empty($post['discount']['type']) || !in_array(strtoupper($post['discount']['type']), ['FIXED', 'PERCENT'])) {
                throw new BadRequestHttpException("Discount type FIXED or PERCENT");
            }
            if (empty($post['discount']['amount'])) {
                throw new BadRequestHttpException("Discount amount empty");
            }
            $order->discount_type = strtoupper($post['discount']['type']) == 'FIXED' ? Order::DISCOUNT_FIXED : Order::DISCOUNT_PERCENT;

            if ($order->discount_type == Order::DISCOUNT_FIXED && $order->getTotalPriceWithOutDiscount() < $post['discount']['amount']) {
                throw new BadRequestHttpException("Discount amount > Total Price");
            }

            if ($order->discount_type == Order::DISCOUNT_PERCENT && 100 < $post['discount']['amount']) {
                throw new BadRequestHttpException("Discount amount > 100%");
            }

            $order->discount = $post['discount']['amount'];
        }

        $tr = \Yii::$app->db->beginTransaction();
        try {
            //Тут операции с продуктами в этом заказе
            if (isset($post['products']) && !empty($post['products'])) {
                if (is_array($post['products'])) {
                    foreach ($post['products'] as $product) {
                        $operation = strtolower($product['operation']);
                        if (empty($operation) or !in_array($operation, ['delete', 'edit', 'add'])) {
                            throw new BadRequestHttpException("I don't know of such an operation: " . $product['operation']);
                        }
                        switch ($operation) {
                            case 'delete':
                                $this->deleteProduct($order, $product['id']);
                                break;
                            case 'add':
                                $this->addProduct($order, $product);
                                break;
                            case 'edit':
                                $this->editProduct($order, $product);
                                break;
                        }
                    }
                } else {
                    throw new BadRequestHttpException("products not array");
                }
            }

            $order->calculateTotalPrice();

            if (!$order->save()) {
                throw new ValidationException($order->getFirstErrors());
            }
            $tr->commit();
            return $this->getInfo(['order_id' => $order->id]);
        } catch (\Exception $e) {
            $tr->rollBack();
            throw $e;
        }
    }

    /**
     * Редактирвание продукта в заказе
     * @param Order $order
     * @param array $product
     * @return bool
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function editProduct(Order $order, array $product)
    {
        if (empty($product['id'])) {
            throw new BadRequestHttpException("EDIT CANCELED product id empty");
        }

        /**
         * @var $orderContent OrderContent
         */
        $orderContent = $order->getOrderContent()->where(['product_id' => $product['id']])->one();
        if (empty($orderContent)) {
            throw new BadRequestHttpException("EDIT CANCELED the product is not found in the order: product_id = " . $product['id']);
        }

        if (!empty($product['quantity'])) {
            $orderContent->quantity = $product['quantity'];
        }

        if (!empty($product['comment'])) {
            $orderContent->comment = $product['comment'];
        }

        if (!empty($product['price'])) {
            $orderContent->price = $product['price'];
        }

        if ($orderContent->validate() && $orderContent->save()) {
            return true;
        } else {
            throw new ValidationException($orderContent->getFirstErrors());
        }
    }

    /**
     * Удаление продукта из заказа
     * @param Order $order
     * @param int $id
     * @throws BadRequestHttpException
     */
    private function deleteProduct(Order $order, int $id)
    {
        if (empty($id)) {
            throw new BadRequestHttpException("DELETE CANCELED product id empty");
        }

        $orderContentRow = $order->getOrderContent()->where(['product_id' => $id])->one();

        if (empty($orderContentRow)) {
            throw new BadRequestHttpException("DELETE CANCELED not found product: " . $id);
        }

        $orderContentRow->delete();
    }

    /**
     * Добавление продукта в заказ
     * @param Order $order
     * @param array $product
     * @return bool
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function addProduct(Order $order, array $product)
    {
        if (empty($product['id'])) {
            throw new BadRequestHttpException("ADD CANCELED product id empty");
        }

        $orderContentRow = $order->getOrderContent()->where(['product_id' => $product['id']])->one();

        if (empty($orderContentRow)) {
            $productModel = (new Product())->findFromCatalogs($product['id'], $this->user->organization->getCatalogs());
            if (!in_array($productModel['supp_org_id'], [$order->vendor->id])) {
                throw new BadRequestHttpException("В этот заказ можно добавлять только товары поставщика: " . $order->vendor->name);
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
                return true;
            } else {
                throw new ValidationException($orderContent->getFirstErrors());
            }
        } else {
            throw new BadRequestHttpException("ADD CANCELED the product is already in the order: " . $product['id']);
        }
    }

    /**
     * Оставляем комментарий к заказу
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function addComment(array $post)
    {
        if (empty($post['order_id'])) {
            throw new BadRequestHttpException('Empty param order_id');
        }

        $order = Order::findOne($post['order_id']);

        if (empty($order)) {
            throw new BadRequestHttpException("Order not found");
        }

        if (!$this->accessAllow($order)) {
            throw new BadRequestHttpException("У вас нет прав на изменение комментария к заказу");
        }

        $order->comment = $post['comment'];

        if (!$order->validate()) {
            throw new ValidationException($order->getFirstErrors());
        }

        $order->save();

        return ['order_id' => $order->id, 'comment' => $order->comment];
    }

    /**
     * Комментарий к конкретному товару в заказе
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function addProductComment(array $post)
    {
        if (empty($post['order_id'])) {
            throw new BadRequestHttpException('Empty param order_id');
        }

        if (empty($post['product_id'])) {
            throw new BadRequestHttpException('Empty param product_id');
        }

        $order = Order::findOne($post['order_id']);

        if (empty($order)) {
            throw new BadRequestHttpException("Order not found");
        }

        if (!$this->accessAllow($order)) {
            throw new BadRequestHttpException("У вас нет прав на изменение комментария к заказу");
        }

        $orderContent = OrderContent::findOne(['order_id' => $order->id, 'product_id' => (int)$post['product_id']]);

        if (empty($orderContent)) {
            throw new BadRequestHttpException("Product not found in Order");
        }

        $orderContent->comment = $post['comment'];

        if (!$orderContent->validate()) {
            throw new ValidationException($orderContent->getFirstErrors());
        }

        $orderContent->save();

        return [
            'order_id' => $order->id,
            'product_id' => $orderContent->product_id,
            'comment' => $orderContent->comment
        ];
    }

    /**
     * Информация о заказе
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getInfo(array $post)
    {
        if (empty($post['order_id'])) {
            throw new BadRequestHttpException('Empty param order_id');
        }

        $order = Order::find()->where(['id' => $post['order_id']])->one();

        if (empty($order)) {
            throw new BadRequestHttpException("Order not found");
        }

        if (!$this->accessAllow($order)) {
            throw new BadRequestHttpException("У вас нет прав для просмотра этого заказа.");
        }

        $result = $order->attributes;
        unset($result['updated_at']);
        unset($result['status']);
        unset($result['accepted_by_id']);
        unset($result['created_by_id']);
        unset($result['vendor_id']);
        unset($result['client_id']);
        unset($result['currency_id']);
        unset($result['discount_type']);
        $result['currency'] = $order->currency->symbol;
        $result['currency_id'] = $order->currency->id;
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

        if (!empty($products)) {
            foreach ($products as $model) {
                /**
                 * @var OrderContent $model
                 */
                $result['items'][] = $this->prepareProduct($model);
            }
        }

        $result['client'] = WebApiHelper::prepareOrganization($order->client);
        $result['vendor'] = WebApiHelper::prepareOrganization($order->vendor);

        return $result;
    }

    /**
     *
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
            ->groupBy('status')
            ->all();

        $return = [
            'waiting' => 0,
            'processing' => 0,
            'success' => 0,
            'canceled' => 0
        ];

        if (!empty($result)) {
            foreach ($result as $row) {
                switch ($row['status']) {
                    case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                    case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                        $return['waiting'] += $row['count'];
                        break;
                    case Order::STATUS_PROCESSING:
                        $return['processing'] += $row['count'];
                        break;
                    case Order::STATUS_DONE:
                        $return['success'] += $row['count'];
                        break;
                    case Order::STATUS_CANCELLED:
                    case Order::STATUS_REJECTED:
                        $return['canceled'] += $row['count'];
                        break;
                }
            }
        }

        return $return;
    }

    /**
     * История заказов
     * @param array $post
     * @return array
     */
    public function getHistory(array $post)
    {
        $sort_field = (!empty($post['sort']) ? $post['sort'] : null);
        $page = (!empty($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (!empty($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $search = new OrderSearch();

        WebApiHelper::clearRequest($post);

        if (isset($post['search'])) {
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

                $orders[] = [
                    'id' => (int)$model->id,
                    'created_at' => \Yii::$app->formatter->asDate($model->created_at, "dd.MM.yyyy"),
                    'completion_date' => \Yii::$app->formatter->asDate($date, "dd.MM.yyyy"),
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

    /**
     * Список доступных для заказа продуктов
     * @param $post
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function products($post)
    {
        $sort = (isset($post['sort']) ? $post['sort'] : null);
        $searchString = (isset($post['search']['product']) ? $post['search']['product'] : null);
        $searchSupplier = (isset($post['search']['supplier_id']) ? $post['search']['supplier_id'] : null);
        $searchCategory = $post['search']['category_id'] ?? null;
        $searchPrice = (isset($post['search']['price']) ? $post['search']['price'] : null);
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $client = $this->user->organization;
        $searchModel = new OrderCatalogSearch();

        $vendors = $client->getSuppliers('', false);
        $catalogs = $vendors ? $client->getCatalogs(null) : "(0)";

        $searchModel->client = $client;
        $searchModel->catalogs = $catalogs;

        /**
         * @var $dataProvider SqlDataProvider
         */
        $searchModel->searchString = $searchString;
        $searchModel->selectedVendor = $searchSupplier;
        $searchModel->searchCategory = $searchCategory;
        $searchModel->searchPrice = $searchPrice;
        $dataProvider = $searchModel->search(['page' => $page, 'pageSize' => $pageSize]);

        //Готовим ответ
        $return = [
            'headers' => [],
            'products' => [],
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

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
                'id' => (int)$model['id'],
                'product' => $model['product'],
                'article' => $model['article'],
                'supplier' => $model['name'],
                'supp_org_id' => (int)$model['supp_org_id'],
                'cat_id' => (int)$model['cat_id'],
                'category_id' => (int)$model['category_id'],
                'price' => round($model['price'], 2),
                'ed' => $model['ed'],
                'units' => (int)$model['units'] ?? 1,
                'currency' => $model['symbol'],
                'currency_id' => (int)$model['currency_id'],
                'image' => @$this->container->get('MarketWebApi')->getProductImage(CatalogBaseGoods::findOne($model['id'])),
                'in_basket' => $this->container->get('CartWebApi')->countProductInCart($model['id']),
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
        return $return;
    }

    /**
     * Отмена заказа
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function cancel(array $post)
    {
        if (empty($post['order_id'])) {
            throw new BadRequestHttpException('Empty param order_id');
        }

        $query = Order::find()->where(['id' => $post['order_id']]);
        if ($this->user->organization->type_id == Organization::TYPE_RESTAURANT) {
            $query->andWhere(['client_id' => $this->user->organization->id]);
        } else {
            $query->andWhere(['vendor_id' => $this->user->organization->id]);
        }
        $order = $query->one();

        if (empty($order)) {
            throw new BadRequestHttpException("Order not found");
        }

        if ($order->status == Order::STATUS_CANCELLED) {
            throw new BadRequestHttpException("This order has been cancelled.");
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $order->status = Order::STATUS_CANCELLED;

            if (!$order->validate() || !$order->save()) {
                throw new ValidationException($order->getFirstErrors());
            }

            if ($this->user->organization->type_id == Organization::TYPE_RESTAURANT) {
                $organization = $order->client;
            } else {
                $organization = $order->vendor;
            }

            Notice::init('Order')->cancelOrder($this->user, $organization, $order);

            $t->commit();
            return $this->getInfo(['order_id' => $order->id]);
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * Повторить заказ
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function repeat(array $post)
    {
        if (empty($post['order_id'])) {
            throw new BadRequestHttpException('Empty param order_id');
        }

        $order = Order::findOne(['id' => $post['order_id'], 'client_id' => $this->user->organization->id]);

        if (empty($order)) {
            throw new BadRequestHttpException("Order not found");
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $content = $order->orderContent;
            if (empty($content)) {
                throw new BadRequestHttpException("Order content is empty.");
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
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function complete(array $post)
    {
        if (empty($post['order_id'])) {
            throw new BadRequestHttpException('Empty param order_id');
        }

        $query = Order::find()->where(['id' => $post['order_id']]);
        if ($this->user->organization->type_id == Organization::TYPE_RESTAURANT) {
            $query->andWhere(['client_id' => $this->user->organization->id]);
        } else {
            $query->andWhere(['vendor_id' => $this->user->organization->id]);
        }
        $order = $query->one();

        if (empty($order)) {
            throw new BadRequestHttpException("Order not found");
        }

        if ($order->status == Order::STATUS_DONE) {
            throw new BadRequestHttpException("This order has been completed.");
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $order->status = Order::STATUS_DONE;
            $order->actual_delivery = gmdate("Y-m-d H:i:s");
            $order->completion_date = new Expression('NOW()');
            if ($order->validate() && $order->save()) {
                Notice::init('Order')->doneOrder($order, $this->user);
            } else {
                throw new ValidationException($order->getFirstErrors());
            }
            $t->commit();
            return $this->getInfo(['order_id' => $order->id]);
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    public function saveToPdf(array $post, OrderController $c)
    {
        if (empty($post['order_id'])) {
            throw new BadRequestHttpException('Empty param order_id');
        }

        $order = Order::findOne(['id' => $post['order_id']]);
        if (empty($order)) {
            throw new BadRequestHttpException('Order not found');
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

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $c->renderPartial('@app/../frontend/views/order/_pdf_order', compact('dataProvider', 'order')),
            'options' => [
                'defaultfooterline' => false,
                'defaultfooterfontstyle' => false,
            ],
            'methods' => [
                'SetFooter' => $c->renderPartial('@app/../frontend/views/order/_pdf_signature'),
            ],
            'cssFile' => '@app/../frontend/web/css/pdf_styles.css'
        ]);
        $pdf->filename = 'mixcart_order_' . $post['order_id'] . '.pdf';
        ob_start();
        $pdf->render();
        $content = ob_get_clean();
        $base64 = (isset($post['base64_encode']) && $post['base64_encode'] == 1 ? true : false);
        return ($base64 ? base64_encode($content) : $content);
    }

    /**
     * @param OrderContent $model
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function prepareProduct(OrderContent $model)
    {
        $quantity = !empty($model->quantity) ? round($model->quantity, 3) : round($model->product->units, 3);

        $item = [];
        $item['id'] = (int)$model->id;
        $item['product'] = $model->product->product;
        $item['product_id'] = isset($model->productFromCatalog->base_goods_id) ? $model->productFromCatalog->base_goods_id : $model->product->id;
        $item['catalog_id'] = isset($model->productFromCatalog->cat_id) ? $model->productFromCatalog->cat_id : $model->product->cat_id;
        $item['price'] = round($model->price, 2);
        $item['quantity'] = $quantity;
        $item['comment'] = $model->comment ?? '';
        $item['total'] = round($model->total, 2);
        $item['rating'] = round($model->product->ratingStars, 1);
        $item['brand'] = ($model->product->brand ? $model->product->brand : '');
        $item['article'] = $model->product->article;
        $item['ed'] = $model->product->ed;
        $item['units'] = $model->product->units;
        $item['currency'] = $model->product->catalog->currency->symbol;
        $item['currency_id'] = (int)$model->product->catalog->currency->id;
        $item['image'] = $this->container->get('MarketWebApi')->getProductImage($model->product);
        return $item;
    }

    /**
     * Список доступных категорий
     * @return array
     */
    public function categories()
    {

        $suppliers = RelationSuppRest::find()
            ->select('supp_org_id')
            ->where(['rest_org_id' => $this->user->organization_id, 'status' => 1])
            ->column();

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
                    'id' => (int)$id,
                    'name' => 'Без категории',
                    'count_product' => MpCategory::getProductCountWithOutCategory(null, $this->user->organization_id)
                ];
                continue;
            }

            $model = MpCategory::findOne($id);
            if (!empty($model->parent)) {
                if (!isset($return[$model->parentCategory->id])) {
                    $return[$model->parentCategory->id] = [
                        'id' => $model->parentCategory->id,
                        'name' => $model->parentCategory->name,
                        'image' => $this->container->get('MarketWebApi')->getCategoryImage($model->parentCategory->id)
                    ];
                }
                $return[$model->parentCategory->id]['subcategories'][] = [
                    'id' => $model->id,
                    'name' => $model->name,
                    'image' => $this->container->get('MarketWebApi')->getCategoryImage($model->id),
                    'count_product' => $model->getProductCount(null, $this->user->organization_id),
                ];
            } else {
                if(!isset($return[$model->id])) {
                    $return[$model->id] = [
                        'id' => $model->id,
                        'name' => $model->name,
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
     * Доступ к изменению заказа
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
}