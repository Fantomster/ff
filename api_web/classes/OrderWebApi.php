<?php

namespace api_web\classes;

use common\models\CatalogBaseGoods;
use common\models\OrderContent;
use common\models\Role;
use common\models\search\OrderCatalogSearch;
use common\models\search\OrderContentSearch;
use common\models\search\OrderSearch;
use common\models\User;
use common\models\Order;
use common\models\Organization;
use api_web\components\Notice;
use yii\data\Pagination;
use yii\data\SqlDataProvider;
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
     */
    public function registration(array $orders)
    {
        /**
         * @var $user User
         * @var $client Organization
         */

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user = $this->user;
            $client = $user->organization;

            if (empty($client->getCartCount())) {
                throw new BadRequestHttpException("Корзина пуста.");
            }

            if (empty($orders['orders'])) {
                throw new BadRequestHttpException("Необходимо передать список заказов для оформления.");
            }

            $return = [];
            foreach ($orders['orders'] as $order_id) {
                $order = Order::findOne([
                    'id' => $order_id,
                    'client_id' => $client->id,
                    'status' => Order::STATUS_FORMING
                ]);

                if (empty($order)) {
                    $return[] = [
                        'order_id' => $order_id,
                        'result' => 'Не найден'
                    ];
                    continue;
                }

                $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                $order->created_by_id = $user->id;
                $order->created_at = gmdate("Y-m-d H:i:s");

                if (!$order->validate()) {
                    throw new ValidationException($order->getFirstErrors());
                }

                $return[] = [
                    'order_id' => $order->id,
                    'result' => $order->save()
                ];

                //Сообщение в очередь поставщику, что есть новый заказ
                Notice::init('Order')->sendOrderToTurnVendor($order->vendor);
                //Емайл и смс о новом заказе
                Notice::init('Order')->sendEmailAndSmsOrderCreated($client, $order);
            }
            //Сообщение в очередь, Изменение количества товара в корзине
            Notice::init('Order')->sendOrderToTurnClient($client);
            $transaction->commit();
            return $return;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
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
            throw new BadRequestHttpException("У вас нет прав на изменение комментария к заказу");
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
        $result['status_id'] = $order->status;
        $result['status_text'] = $order->statusText;
        $result['position_count'] = (int)$order->positionCount;
        $result['delivery_price'] = round($order->calculateDelivery(), 2);
        $result['min_order_price'] = round($order->forMinOrderPrice(), 2);
        $result['total_price_without_discount'] = round($order->getTotalPriceWithOutDiscount(), 2);

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

        $result['client'] = $this->container->get('MarketWebApi')->prepareOrganization($order->client);
        $result['vendor'] = $this->container->get('MarketWebApi')->prepareOrganization($order->vendor);

        return $result;
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
                    'create_user' => $model->createdByProfile->full_name
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

            if ($field == 'supplier') {
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
            return ['result' => true];
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
            $result = $this->container->get('CartWebApi')->add($request);
            $t->commit();
            return $result;
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
        $item['currency'] = $model->product->catalog->currency->symbol;
        $item['currency_id'] = (int)$model->product->catalog->currency->id;
        $item['image'] = $this->container->get('MarketWebApi')->getProductImage($model->product);
        return $item;
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