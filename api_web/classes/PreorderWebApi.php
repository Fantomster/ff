<?php
/**
 * Date: 04.02.2019
 * Author: Mike N.
 * Time: 14:35
 */

namespace api_web\classes;

use api_web\{components\Registry,
    helpers\WebApiHelper,
    exceptions\ValidationException,
    components\WebApi,
    components\Notice,
    helpers\CurrencyHelper};
use common\models\{CartContent,
    CatalogBaseGoods,
    Order,
    OrderStatus,
    Preorder,
    Cart,
    Organization,
    PreorderContent,
    ProductAnalog,
    Profile,
    OrderContent,
    CatalogGoods,
    RelationSuppRest};
use yii\data\{
    ArrayDataProvider,
    Pagination
};

use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use yii\web\BadRequestHttpException;

/**
 * Class PreorderWebApi
 *
 * @package api_web\classes
 */
class PreorderWebApi extends WebApi
{

    private $arAvailableFields = [
        'id',
        'sum',
        'user',
    ];

    const DISABLED_EDIT_ORDER_STATUS = [
        Registry::MC_BACKEND     => [
            Order::STATUS_CANCELLED,
            Order::STATUS_REJECTED
        ],
        Registry::EDI_SERVICE_ID => [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_PROCESSING,
            Order::STATUS_CANCELLED,
            Order::STATUS_REJECTED,
            Order::STATUS_EDI_SENT_BY_VENDOR,
            Order::STATUS_EDI_ACCEPTANCE_FINISHED
        ]
    ];

    /**
     * @param array $vendors
     * @param Cart  $cart
     * @return Preorder
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \Throwable
     */
    private function createPreorder(array $vendors, Cart $cart)
    {
        $preOrder = new Preorder([
            'organization_id' => $this->user->organization->id,
            'user_id'         => $this->user->id
        ]);
        if (!$preOrder->save()) {
            throw new ValidationException($preOrder->getFirstErrors());
        }
        $cartWebApi = new CartWebApi();
        foreach ($vendors as $vendor) {
            $contents = $cart->getCartContents()->andWhere(['vendor_id' => $vendor->id])->all();
            if (empty($contents)) {
                throw new BadRequestHttpException('preorder.no_vendor_product_in_cart');
            }
            if ($cartWebApi->createOrder($cart, $vendor, [], Order::STATUS_PREORDER, $preOrder->id)) {
                /** @var CartContent $item */
                foreach (WebApiHelper::generator($contents) as $item) {
                    $preOrderContent = new PreorderContent([
                        'preorder_id'   => $preOrder->id,
                        'product_id'    => $item->product_id,
                        'plan_quantity' => $item->quantity
                    ]);

                    $parent_id = $this->getFirstProductAnalog($item->product_id);
                    if ($parent_id) {
                        $preOrderContent->parent_product_id = $parent_id;
                    }
                    if (!$preOrderContent->save()) {
                        throw new ValidationException($preOrderContent->getFirstErrors());
                    }
                }
            }
        }
        return $preOrder;
    }

    /**
     * Создание предзаказа из корзины
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \Throwable
     */
    public function create($post)
    {
        $cart = Cart::findOne(['organization_id' => $this->user->organization->id]);
        if (empty($cart)) {
            throw new BadRequestHttpException('preorder.cart_was_not_found');
        }
        if (!empty($post['vendor_id']) && ($post['vendor_id'] !== 0)) {
            $myVendors = $this->user->organization->getSuppliers();
            if (!isset($myVendors[$post['vendor_id']])) {
                throw new BadRequestHttpException('preorder.not_your_vendor');
            }
            $vendor = Organization::findOne(['id' => $post['vendor_id']]);
            $vendors[] = $vendor;
        } else {
            $vendors = $cart->getVendors();
            if (empty($vendors)) {
                throw new BadRequestHttpException('preorder.cart_empty');
            }
        }
        $preOrder = $this->createPreorder($vendors, $cart);
        return $this->prepareModel($preOrder);
    }

    /**
     * Меняет статус предзаказа на неактивный
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function complete($post)
    {
        $this->validateRequest($post, ['id']);
        $model = $this->findPreOrder($post['id']);
        $model->is_active = 0;
        if ($model->save()) {
            return $this->prepareModel($model);
        } else {
            throw new ValidationException($model->getFirstErrors());
        }
    }

    /**
     * Список предзаказов
     *
     * @param $request
     * @return array
     */
    public function list($request)
    {
        $page = $request['pagination']['page'] ?? 1;
        $pageSize = $request['pagination']['page_size'] ?? 12;
        $sort = $request['sort'] ?? '-id';
        $result = [];

        $tableName = Preorder::tableName();
        $tableNameProfile = Profile::tableName();
        $tableNameOrder = Order::tableName();
        $sumExpression = new Expression("SUM({$tableNameOrder}.total_price)");
        $models = Preorder::find()
            ->joinWith(['orders', 'user.profile'])
            ->select("{$tableName}.*")
            ->addSelect([
                'sum'  => $sumExpression,
                'user' => "{$tableNameProfile}.full_name"
            ])
            ->where(["{$tableName}.organization_id" => $this->user->organization_id])
            ->groupBy("{$tableName}.id");

        if (isset($request['search'])) {
            //Поисковая строка
            if (!empty($request['search']['query'])) {
                $models->andFilterWhere(['OR',
                    ['like', "{$tableName}.id", $request['search']['query'], false],
                    ['like', "{$tableNameProfile}.full_name", $request['search']['query']],
                ]);
            }
            //Фильтр по статусу
            if (isset($request['search']['status'])) {
                $is_active = $request['search']['status'] ?? null;
                if (!is_null($is_active)) {
                    $models->andFilterWhere(["{$tableName}.is_active" => (int)$is_active]);
                }
            }
            //Фильтр по сумме
            if (!empty($request['search']['price'])) {
                $priceFrom = $request['search']['price']['from'] ?? null;
                $priceTo = $request['search']['price']['to'] ?? null;
                if (!is_null($priceFrom)) {
                    $models->andHaving(['>', 'sum', $priceFrom]);
                }
                if (!is_null($priceTo)) {
                    $models->andHaving(['<', 'sum', $priceTo]);
                }
            }
            //Фильтр по дате
            if (!empty($request['search']['date'])) {
                $dateFrom = WebApiHelper::asDatetime($request['search']['date']['from'] ?? null);
                $dateTo = WebApiHelper::asDatetime($request['search']['date']['to'] ?? null);
                if (!is_null($dateFrom)) {
                    $models->andWhere(['>', "{$tableName}.created_at", $dateFrom]);
                }
                $models->andWhere(['<', "{$tableName}.created_at", WebApiHelper::asDatetime($dateTo)]);
            }
        }

        if ($models->count()) {
            if ($sort && in_array(ltrim($sort, '-'), $this->arAvailableFields)) {
                $sortDirection = SORT_ASC;
                if (strpos($sort, '-') !== false) {
                    $sortDirection = SORT_DESC;
                }
                $models->orderBy([ltrim($sort, '-') => $sortDirection]);
            }

            $dataProvider = new ArrayDataProvider([
                'allModels' => $models->all()
            ]);

            $pagination = new Pagination();
            $pagination->setPage($page - 1);
            $pagination->setPageSize($pageSize);
            $dataProvider->setPagination($pagination);
            /** @var Preorder $model */
            if (!empty($dataProvider->models)) {
                foreach (WebApiHelper::generator($dataProvider->models) as $model) {
                    $result[] = $this->prepareModel($model);
                }
            }
            $page = ($dataProvider->pagination->page + 1);
            $pageSize = $dataProvider->pagination->pageSize;
            $totalPage = ceil($dataProvider->totalCount / $pageSize);
        }

        $return = [
            'items'      => $result,
            'pagination' => [
                'page'       => $page,
                'page_size'  => $pageSize,
                'total_page' => $totalPage ?? 0
            ],
            'sort'       => $sort
        ];

        return $return;
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function orders($request)
    {
        $this->validateRequest($request, ['id']);
        $model = Preorder::findOne([
            'id'              => (int)$request['id'],
            'organization_id' => $this->user->organization_id
        ]);
        if (empty($model)) {
            throw new BadRequestHttpException('preorder.not_found');
        }

        $items = [];
        $orders = $model->orders;
        if (!empty($orders)) {
            $orderWebApi = new OrderWebApi();
            /** @var Order $order */
            foreach (WebApiHelper::generator($orders) as $order) {
                $items[] = $orderWebApi->getOrderInfo($order);
            }
        }

        return [
            'items' => $items
        ];
    }

    /**
     * @param Preorder $preOrder
     * @return array
     */
    private function productsInfo(Preorder $preOrder)
    {
        $products = [];
        $productsArray = (new Query())
            ->select([
                'plan_quantity' => new Expression("sum(coalesce(a.plan_quantity * b.coefficient, a.plan_quantity))"),
                'product_id'    => new Expression("coalesce(c.product_id, b.product_id, a.product_id)"),
                'id'            => new Expression("MIN(a.id)"),
                'has_analogs'   => new Expression("case when b.product_id is not null then 1 else 0 end"),
            ])
            ->from(PreorderContent::tableName() . ' as a')
            ->leftJoin(ProductAnalog::tableName() . ' as b', 'a.product_id = b.product_id')
            ->leftJoin(ProductAnalog::tableName() . ' as c', 'c.id = b.parent_id')
            ->where(['a.preorder_id' => $preOrder->id])
            ->groupBy(new Expression("coalesce(c.product_id, b.product_id, a.product_id)"))
            ->indexBy('id')
            ->all();

        /** @var PreorderContent[] $contents */
        $contents = $preOrder->getPreorderContents()->onCondition(['in', 'id', array_keys($productsArray)])->all();
        if ($contents) {
            /** @var PreorderContent $content */
            foreach (WebApiHelper::generator($contents) as $content) {
                if ($content->product_id != $productsArray[$content->id]['product_id']) {
                    $product = CatalogBaseGoods::findOne($productsArray[$content->id]['product_id']);
                } else {
                    $product = $content->product;
                }
                $products[] = [
                    'id'            => (int)$content->product_id,
                    'name'          => $product->product,
                    'article'       => $product->article,
                    'plan_quantity' => round($productsArray[$content->id]['plan_quantity'], 3),
                    'quantity'      => $content->getAllQuantity(),
                    'sum'           => CurrencyHelper::asDecimal($content->getAllSum()),
                    'isset_analog'  => (bool)$productsArray[$content->id]['has_analogs']
                ];
            }
        }
        return $products;
    }

    /**
     * @param $product_ids
     * @return array
     */
    private function issetProductsAnalog($product_ids)
    {
        return ProductAnalog::find()
            ->where([
                'client_id'  => $this->user->organization_id,
                'product_id' => $product_ids
            ])
            ->indexBy('product_id')
            ->asArray()
            ->all();
    }

    /**
     * @param $product_id
     * @return false|string|null
     */
    private function getFirstProductAnalog($product_id)
    {
        $r = (new Query())
            ->select('b.product_id')
            ->from(ProductAnalog::tableName() . ' as a')
            ->leftJoin(ProductAnalog::tableName() . ' as b', 'b.id = a.parent_id')
            ->where([
                'a.product_id' => $product_id,
                'a.client_id'  => $this->user->organization_id
            ])
            ->scalar();
        return ($r > 0) ? (int)$r : null;
    }

    /**
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function get($post)
    {
        $this->validateRequest($post, ['id']);
        $model = $this->findPreOrder($post['id']);
        return $this->prepareModel($model, true);
    }

    /**
     * Оформление заказов
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function confirmOrders($post)
    {
        $this->validateRequest($post, ['id']);
        $model = Preorder::findOne([
            'id'              => (int)$post['id'],
            'organization_id' => $this->user->organization_id
        ]);
        if (empty($model)) {
            throw new BadRequestHttpException('preorder.not_found');
        }
        $orders = $model->orders;
        if (!empty($orders)) {
            /** @var Order $order */
            foreach (WebApiHelper::generator($orders) as $order) {
                if (empty($order->requested_delivery)) {
                    throw new BadRequestHttpException('orders.requested_delivery_empty');
                }
                $order->status = OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                if (!$order->save()) {
                    throw new ValidationException($model->getFirstErrors());
                }
                //Емайл и смс о новом заказе
                try {
                    Notice::init('Order')->sendEmailAndSmsOrderCreated($this->user->organization, $order);
                    //Сообщение в очередь поставщику, что есть новый заказ
                    Notice::init('Order')->sendOrderToTurnVendor($order->vendor);
                    //Сообщение в очередь, Изменение количества товара в корзине
                    Notice::init('Order')->sendOrderToTurnClient($this->user);
                } catch (\Exception $e) {
                    \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }
            }
        } else {
            throw new BadRequestHttpException('order.not_found');
        }
        return $this->orders(['id' => $model->id]);
    }

    /**
     * Подтверждение одного заказа
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function confirmOrder($request)
    {
        $this->validateRequest($request, ['id', 'order_id']);

        $model = Preorder::findOne([
            'id'              => (int)$request['id'],
            'organization_id' => $this->user->organization_id
        ]);
        if (empty($model)) {
            throw new BadRequestHttpException('preorder.not_found');
        }

        /** @var Order $order */
        $order = $model->getOrders()->andWhere(['order.id' => $request['order_id']])->one();
        if (empty($order)) {
            throw new BadRequestHttpException('order.not_found');
        }

        if (empty($order->requested_delivery)) {
            throw new BadRequestHttpException('order.requested_delivery_empty');
        }

        $order->status = OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
        if (!$order->save()) {
            throw new ValidationException($model->getFirstErrors());
        }
        //Емайл и смс о новом заказе
        try {
            Notice::init('Order')->sendEmailAndSmsOrderCreated($this->user->organization, $order);
            //Сообщение в очередь поставщику, что есть новый заказ
            Notice::init('Order')->sendOrderToTurnVendor($order->vendor);
            //Сообщение в очередь, Изменение количества товара в корзине
            Notice::init('Order')->sendOrderToTurnClient($this->user);
        } catch (\Exception $e) {
            \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        return (new OrderWebApi())->getOrderInfo($order);
    }

    /**
     * Подготовка модели к выдаче фронту
     *
     * @param Preorder $model
     * @param bool     $products
     * @return array
     */
    private function prepareModel(Preorder $model, bool $products = false)
    {
        $return = [
            'id'           => $model->id,
            'is_active'    => (bool)$model->is_active,
            'organization' => [
                'id'   => $model->organization->id,
                'name' => $model->organization->name
            ],
            'user'         => [
                'id'   => $model->user->id,
                'name' => $model->user->profile->full_name
            ],
            'count'        => [
                'products' => (int)$model->getPreorderContents()->count(),
                'orders'   => (int)$model->getOrders()->count(),
            ],
            'sum'          => $model->getSum(),
            'currency'     => [
                'id'     => $model->currency->id,
                'symbol' => $model->currency->symbol,
            ],
            'created_at'   => WebApiHelper::asDatetime($model->created_at),
            'updated_at'   => WebApiHelper::asDatetime($model->updated_at)
        ];

        if ($products) {
            $return['products'] = $this->productsInfo($model);
        }

        return $return;
    }

    /**
     * @param int  $id
     * @param bool $withContent
     * @return array|Preorder|\yii\db\ActiveRecord|null
     * @throws BadRequestHttpException
     */
    private function findPreOrder(int $id, bool $withContent = false)
    {
        if (!$withContent) {
            $model = Preorder::findOne([
                'id'              => $id,
                'organization_id' => $this->user->organization_id,
            ]);
        } else {
            $model = Preorder::find()
                ->where([
                    'id'              => $id,
                    'organization_id' => $this->user->organization_id,
                ])
                ->with('preorderContents')
                ->one();
        }
        if (empty($model)) {
            throw new BadRequestHttpException('preorder.not_found');
        }
        return $model;
    }

    /**
     * Добавление новых продуктов в предзаказ
     *
     * @param array $preOrderContent
     * @param int   $preorderId
     * @param       $planQuantity
     * @throws \Exception
     */
    private function createPreOrderContent(array $preOrderContent, int $preorderId, $planQuantity)
    {
        $newData = [];
        foreach ($preOrderContent as $index => $product) {
            $parentProductId = $product['parent_product_id'] ?? $this->getFirstProductAnalog($product['id']);
            $newData[] = [
                'preorder_id'       => $preorderId,
                'product_id'        => $product['id'],
                'plan_quantity'     => $planQuantity ? $product['quantity'] : 0,
                'parent_product_id' => $product['id'] == $parentProductId ? null : $parentProductId
            ];
        }
        try {
            foreach (WebApiHelper::generator($newData) as $row) {
                $findRow = $row;
                unset($findRow['plan_quantity']);
                $exists = PreorderContent::find()->where($findRow)->exists();
                if (!$exists) {
                    $model = new PreorderContent([
                        'preorder_id'   => $preorderId,
                        'product_id'    => $row['product_id'],
                        'plan_quantity' => $row['plan_quantity'],
                    ]);
                    if (isset($row['parent_product_id'])) {
                        $model->parent_product_id = (int)$row['parent_product_id'];
                    }
                    if (!$model->save()) {
                        throw new ValidationException($model->getFirstErrors());
                    }
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param      $post
     * @param bool $planQuantity
     * @return array
     * @throws
     */
    public function addProduct($post, $planQuantity = true)
    {
        $this->validateRequest($post, ['id', 'products']);
        if (!is_array($post['products'])) {
            throw new BadRequestHttpException('preorder.wrong_value_type');
        }
        //получаем предзаказ данного пользователя
        $preOrder = $this->findPreOrder($post['id'], true);

        $vendorIds = ArrayHelper::getColumn($post['products'], 'vendor_id');
        $vendorProducts = [];
        foreach ($post['products'] as $product) {
            $vendorProducts[$product['vendor_id']][] = $product;
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            //Получаем массив поставщиков данной организации
            $vendorsWork = $this->user->organization->getSuppliers('', false);
            foreach ($vendorIds as $vendorId) {
                if (!isset($vendorsWork[$vendorId])) {
                    throw new BadRequestHttpException('vendor.not_found');
                }
                $vendor = Organization::findOne($vendorId);
                $products = $vendorProducts[$vendor->id];
                if (!empty($products)) {
                    //Создаем записи о товарах в preorder_content
                    $this->createPreOrderContent($products, $preOrder->id, $planQuantity);
                    $service_id = $vendor->isEdi() ? Registry::EDI_SERVICE_ID : Registry::MC_BACKEND;
                    $this->handleOrder($vendor, $products, $preOrder, $service_id);
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }

        $preOrder->refresh();
        return $this->prepareModel($preOrder, true);
    }

    /**
     * @param Organization $vendor
     * @param array        $products
     * @param Preorder     $preOrder
     * @param              $service_id
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function handleOrder(Organization $vendor, array $products, Preorder $preOrder, $service_id)
    {
        $order = $preOrder->getOrders()
            ->andWhere(['vendor_id' => $vendor->id])
            ->andWhere(['not in', 'status', self::DISABLED_EDIT_ORDER_STATUS[$service_id]])
            ->one();

        $relation = $this->findRelation($vendor->id);
        if (!$relation) {
            throw new BadRequestHttpException('relation.not_found');
        }

        if (!$order) {
            $currency_id = $relation->catalog->currency_id ?? Registry::DEFAULT_CURRENCY_ID;
            $order = $this->createOrder($vendor->id, $preOrder->id, $currency_id);
        }

        foreach (WebApiHelper::generator($products) as $product) {
            $productModel = CatalogGoods::findOne([
                'base_goods_id' => $product['id'],
                'cat_id'        => $relation->cat_id
            ]);
            if (!$productModel) {
                throw new BadRequestHttpException('product.not_found');
            }
            $this->createOrderContent($order, $productModel, $product['quantity']);
        }
    }

    /**
     * @param int $vendorId
     * @param int $preOrderId
     * @param     $currency_id
     * @return Order
     * @throws ValidationException
     */
    private function createOrder(int $vendorId, int $preOrderId, $currency_id)
    {
        $client = $this->user->organization;
        //Создаем заказ
        $order = new Order();
        $order->client_id = $client->id;
        $order->created_by_id = $this->user->id;
        $order->vendor_id = $vendorId;
        $order->status = Order::STATUS_PREORDER;
        $order->currency_id = $currency_id;
        $order->service_id = Registry::MC_BACKEND;
        $order->preorder_id = $preOrderId;
        if (!$order->save()) {
            \Yii::error(\yii\helpers\Json::encode($order->getErrors()));
            throw new ValidationException($order->getFirstErrors());
        }
        return $order;
    }

    /**
     * @param Order        $order
     * @param CatalogGoods $product
     * @param              $quantity
     * @throws ValidationException
     */
    private function createOrderContent(Order $order, CatalogGoods $product, $quantity)
    {
        if ($order->isNewRecord) {
            $orderContent = new OrderContent();
        } else {
            $orderContent = OrderContent::findOne([
                'order_id'   => $order->id,
                'product_id' => $product->base_goods_id
            ]);
            if (!$orderContent) {
                $orderContent = new OrderContent();
            }
        }

        if ($orderContent->isNewRecord) {
            $orderContent->order_id = $order->id;
            $orderContent->product_id = $product->base_goods_id;
            $orderContent->plan_quantity = $quantity;
            $orderContent->initial_quantity = $quantity;
            $orderContent->price = $product->price ?? 0;
            $orderContent->plan_price = $product->price ?? 0;
            $orderContent->product_name = $product->baseProduct->product;
            $orderContent->units = $product->baseProduct->units;
            $orderContent->article = $product->baseProduct->article;
        }

        $orderContent->quantity = $quantity;

        if (!$orderContent->save()) {
            \Yii::error(\yii\helpers\Json::encode($orderContent->getErrors()));
            throw new ValidationException($orderContent->getFirstErrors());
        }
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function updateProduct($request)
    {
        $this->validateRequest($request, ['id']);
        $orderContent = OrderContent::findOne(['id' => $request['id']]);
        if (!$orderContent) {
            throw new BadRequestHttpException('order_content.not_found');
        }
        $orderInfo = null;
        $order = $orderContent->order;

        $status = self::DISABLED_EDIT_ORDER_STATUS[$order->service_id];
        if ($order->service_id == Registry::EDI_SERVICE_ID) {
            unset($status[Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]);
        }

        if (in_array($order->status, $status)) {
            throw new BadRequestHttpException('order.status_canceled');
        }

        $orderDelete = [];
        $t = \Yii::$app->db->beginTransaction();
        try {
            $orderContent->quantity = $request['quantity'];
            if ($orderContent->quantity == 0) {
                $is = $this->issetProductsAnalog($orderContent->product_id);
                if (isset($is[$orderContent->product_id])) {
                    if (!$orderContent->delete()) {
                        throw new Exception('Delete false');
                    }
                } elseif (!$orderContent->save()) {
                    throw new ValidationException($orderContent->getFirstErrors());
                }
            } else {
                if (!$orderContent->save()) {
                    throw new ValidationException($orderContent->getFirstErrors());
                }
            }
            if (empty($order->orderContent)) {
                $orderDelete[] = $order->id;
                $order->delete();
            } else {
                $order->calculateTotalPrice();
                $orderInfo = (new OrderWebApi())->getOrderInfo($order);
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }

        return [
            'preorder'     => $this->get(['id' => $order->preorder_id]),
            'order'        => $orderInfo,
            'order_delete' => $orderDelete
        ];
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function updateOrder($request)
    {
        $this->validateRequest($request, ['order_id']);
        $order = Order::findOne(['id' => $request['order_id']]);
        if (!$order) {
            throw new BadRequestHttpException('order.not_found');
        }

        if (!empty($request['requested_delivery'])) {
            $order->requested_delivery = date('Y-m-d', strtotime($request['requested_delivery']));
        }

        if (!empty($request['comment'])) {
            $order->comment = $request['comment'];
        }

        if (!$order->save()) {
            throw new ValidationException($order->getFirstErrors());
        }

        return (new OrderWebApi())->getOrderInfo($order);
    }

    /**
     * Кнопка Очистить
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function orderClear($request)
    {
        $orderDelete = [];
        $orderInfo = null;
        $this->validateRequest($request, ['order_id']);
        $order = Order::findOne(['id' => $request['order_id'], 'status' => Order::STATUS_PREORDER]);
        if (!$order) {
            throw new BadRequestHttpException('order.not_found');
        }

        $orderContents = $order->orderContent;
        $productsId = ArrayHelper::getColumn($orderContents, 'product_id');
        $issetAnalog = $this->issetProductsAnalog($productsId);
        $t = \Yii::$app->db->beginTransaction();
        try {
            /** @var OrderContent $orderContent */
            foreach (WebApiHelper::generator($orderContents) as $orderContent) {
                if (isset($issetAnalog[$orderContent->product_id])) {
                    $orderContent->delete();
                    PreorderContent::deleteAll([
                        'preorder_id'       => $order->preorder_id,
                        'product_id'        => $orderContent->product_id,
                        'parent_product_id' => $this->getFirstProductAnalog($orderContent->product_id)
                    ]);
                } else {
                    $orderContent->quantity = 0;
                    if (!$orderContent->save()) {
                        throw new ValidationException($orderContent->getFirstErrors());
                    }
                }
            }
            $order->refresh();
            if (empty($order->orderContent)) {
                $orderDelete[] = $order->id;
                $order->delete();
            } else {
                $order->calculateTotalPrice();
                $orderInfo = (new OrderWebApi())->getOrderInfo($order);
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }

        return [
            'preorder'     => $this->get(['id' => $order->preorder_id]),
            'order'        => $orderInfo,
            'order_delete' => $orderDelete
        ];
    }

    /**
     * Кнопка Повторить заказ
     *
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function orderRepeat($request)
    {
        $this->validateRequest($request, ['order_id']);
        $order = Order::find()
            ->where([
                'id' => $request['order_id'],
            ])
            ->andWhere('preorder_id is not null')
            ->one();

        if (!$order) {
            throw new BadRequestHttpException('order.not_found');
        }

        if (!in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_REJECTED])) {
            throw new BadRequestHttpException('preorder.repeat_order_not_canceled');
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $newOrder = new Order();
            $newOrder->setAttributes($order->getAttributes([
                'client_id',
                'currency_id',
                'service_id',
                'vendor_id',
                'comment'
            ]));

            $newOrder->created_by_id = $this->user->id;
            $newOrder->status = Order::STATUS_PREORDER;
            $newOrder->preorder_id = $order->preorder_id;

            if (!$newOrder->save()) {
                throw new ValidationException($newOrder->getFirstErrors());
            }

            $orderContents = $order->orderContent;
            foreach (WebApiHelper::generator($orderContents) as $orderContent) {
                $nOrderContent = new OrderContent();
                $nOrderContent->setAttributes($orderContent->getAttributes([
                    'article', 'initial_quantity', 'into_price',
                    'into_price_sum', 'into_price_sum_vat', 'into_price_vat',
                    'into_quantity', 'merc_uuid', 'plan_price',
                    'plan_quantity', 'price', 'product_id',
                    'product_name', 'quantity', 'units',
                    'vat_product'
                ]));

                $nOrderContent->order_id = $newOrder->id;
                if (!$nOrderContent->save()) {
                    throw new ValidationException($nOrderContent->getFirstErrors());
                }
            }
            $newOrder->calculateTotalPrice();
            $t->commit();
            $result = [
                'preorder' => $this->get(['id' => $newOrder->preorder_id]),
                'order'    => (new OrderWebApi())->getOrderInfo($newOrder)
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Кнопка Отменить заказ
     *
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function orderCancel($request)
    {
        $this->validateRequest($request, ['order_id']);
        $order = Order::find()
            ->where([
                'id' => $request['order_id']
            ])
            ->andWhere('preorder_id is not null')
            ->one();

        if (!$order) {
            throw new BadRequestHttpException('order.not_found');
        }

        if (in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_REJECTED])) {
            throw new BadRequestHttpException('Заказ уже отменен');
        }

        if (!in_array($order->service_id, [Registry::MC_BACKEND, Registry::EDI_SERVICE_ID])) {
            throw new BadRequestHttpException('Этот тип заказов тут не должен быть вообще!!!');
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            switch ($order->service_id) {
                case Registry::EDI_SERVICE_ID:
                    $class = EdiWebApi::class;
                    $method = 'orderCancel';
                    break;
                default:
                    $class = OrderWebApi::class;
                    $method = 'cancel';
            }

            $webApi = new $class();
            $webApi->{$method}(['order_id' => $order->id]);
            $t->commit();
            $order->refresh();
            $result = [
                'preorder' => $this->get(['id' => $order->preorder_id]),
                'order'    => (new OrderWebApi())->getOrderInfo($order)
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Добавить аналог продукта
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function addAnalogProduct($request)
    {
        $this->validateRequest($request, ['preorder_id', 'product_id', 'analog_id', 'vendor_id']);

        if (!isset($request['quantity'])) {
            throw new BadRequestHttpException("empty_param|quantity");
        }

        $preOrder = $this->findPreOrder($request['preorder_id']);
        $relation = $this->findRelation($request['vendor_id']);

        $analog = CatalogGoods::findOne([
            'cat_id'        => $relation->cat_id,
            'base_goods_id' => $request['analog_id']
        ]);

        $t = \Yii::$app->db->beginTransaction();
        try {
            $orderDelete = [];
            if ($request['quantity'] == 0) {
                $orders = $preOrder->getOrders()->andWhere([
                    'vendor_id' => $relation->supp_org_id
                ])->all();
                /** @var Order $order */
                foreach (WebApiHelper::generator($orders) as $order) {
                    $orderContent = $order->getOrderContent()->andWhere(['product_id' => $analog->base_goods_id])->one();
                    if ($orderContent) {
                        $r = $this->updateProduct([
                            'id'       => $orderContent->id,
                            'quantity' => 0
                        ]);
                        PreorderContent::deleteAll([
                            'preorder_id'       => $preOrder->id,
                            'product_id'        => $analog->base_goods_id,
                            'parent_product_id' => $this->getFirstProductAnalog($analog->base_goods_id)
                        ]);
                        $orderDelete = ArrayHelper::merge($orderDelete, $r['order_delete']);
                    }
                }
                $result = $this->get(['id' => $preOrder->id]);
            } else {
                $result = $this->addProduct([
                    'id'       => $preOrder->id,
                    'products' => [
                        [
                            'id'                => (int)$analog->base_goods_id,
                            'cat_id'            => (int)$analog->cat_id,
                            'vendor_id'         => (int)$relation->supp_org_id,
                            'parent_product_id' => $this->getFirstProductAnalog($analog->base_goods_id),
                            'quantity'          => round($request['quantity'], 3)
                        ]
                    ]
                ], false);
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }

        return [
            'preorder'     => $result,
            'order_delete' => $orderDelete
        ];;
    }

    /**
     * @param $vendor_id
     * @return RelationSuppRest|null
     * @throws BadRequestHttpException
     */
    private function findRelation($vendor_id)
    {
        $relation = RelationSuppRest::findOne([
            'rest_org_id' => $this->user->organization_id,
            'supp_org_id' => $vendor_id
        ]);

        if (empty($relation)) {
            throw new BadRequestHttpException('relation.not_found');
        }

        return $relation;
    }
}
