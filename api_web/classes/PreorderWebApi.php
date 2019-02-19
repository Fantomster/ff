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
use common\models\{Order,
    OrderStatus,
    Preorder,
    Cart,
    Organization,
    PreorderContent,
    ProductAnalog,
    Profile,
    OrderContent,
    Catalog,
    CatalogGoods};
use yii\data\{
    ArrayDataProvider,
    Pagination
};

use yii\db\Exception;
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
        $preOrder = new Preorder();
        $preOrder->organization_id = $this->user->organization->id;
        $preOrder->user_id = $this->user->id;
        $preOrder->is_active = 1;
        if (!$preOrder->save()) {
            throw new ValidationException($preOrder->getFirstErrors());
        }
        $cartWebApi = new CartWebApi();
        $noCommentAndDate = [];
        $preOrderId = $preOrder->id;
        foreach ($vendors as $index => $vendor) {
            $contents = $cart->getCartContents()->andWhere(['vendor_id' => $vendor->id])->all();
            if (empty($contents)) {
                throw new BadRequestHttpException('preorder.no_vendor_product_in_cart');
            }
            if ($cartWebApi->createOrder($cart, $vendor, $noCommentAndDate, Order::STATUS_PREORDER, $preOrderId)) {
                foreach ($contents as $key => $item) {
                    $preOrderContent = new PreorderContent();
                    $preOrderContent->preorder_id = $preOrderId;
                    $preOrderContent->product_id = $item->product_id;
                    $preOrderContent->plan_quantity = $item->quantity;
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
            $vendor = Organization::findOne(['id' => $post['vendor_id'], 'type_id' => Organization::TYPE_SUPPLIER]);
            if (empty($vendor)) {
                throw new BadRequestHttpException('preorder.vendor_id_not_found');
            }
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
        $model = Preorder::findOne([
            'id'              => (int)$post['id'],
            'organization_id' => $this->user->organization_id
        ]);
        if (empty($model)) {
            throw new BadRequestHttpException('preorder.not_found');
        }
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
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
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
        /** @var PreorderContent[] $contents */
        $contents = $preOrder->preorderContents;
        if ($contents) {
            $productIds = ArrayHelper::getColumn($contents, 'product_id');
            $issetAnalog = $this->issetProductsAnalog($productIds);
            /** @var PreorderContent $order */
            foreach ($contents as $content) {
                $products[] = [
                    'id'            => $content->product_id,
                    'name'          => $content->product->product,
                    'article'       => $content->product->article,
                    'plan_quantity' => round($content->plan_quantity, 3),
                    'quantity'      => $content->getAllQuantity(),
                    'sum'           => CurrencyHelper::asDecimal($content->getAllSum()),
                    'isset_analog'  => $issetAnalog[$content->product_id] ?? false,
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
        return ProductAnalog::find()->where([
            'client_id'  => $this->user->organization_id,
            'product_id' => $product_ids
        ])->indexBy('product_id')
            ->asArray()
            ->all();
    }

    /**
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function get($post)
    {
        $this->validateRequest($post, ['id']);
        $model = Preorder::findOne([
            'id'              => (int)$post['id'],
            'organization_id' => $this->user->organization_id
        ]);
        if (empty($model)) {
            throw new BadRequestHttpException('preorder.not_found');
        }
        return $this->prepareModel($model, true);
    }

    /**
     * Оформление заказов
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
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
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
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
    private function findPreorder(int $id, bool $withContent = false)
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
     * Добавление данных в заказ
     *
     * @param $orderId
     * @param $productId
     * @param $quantity
     * @param $price
     * @param $productName
     * @param $units
     * @param $article
     * @return bool
     * @throws ValidationException
     */
    private function createOrderContent($orderId, $productId, $quantity, $price, $productName, $units, $article)
    {
        $orderContent = new OrderContent();
        $orderContent->order_id = $orderId;
        $orderContent->product_id = $productId;
        $orderContent->quantity = $quantity;
        $orderContent->plan_quantity = $quantity;
        $orderContent->initial_quantity = $quantity;
        $orderContent->price = $price;
        $orderContent->plan_price = $price;
        $orderContent->product_name = $productName;
        $orderContent->units = $units;
        $orderContent->article = $article;
        if ($orderContent->validate() && $orderContent->save()) {
            return true;
        } else {
            \Yii::error(\yii\helpers\Json::encode($orderContent->getErrors()));
            throw new ValidationException($orderContent->getFirstErrors());
        }
    }

    /**
     * @param array $productSorted1
     * @param array $orders
     * @param array $productSorted2
     * @param array $catalogGoods
     * @param array $vendorNeeded
     * @param array $catalogNeeded
     * @throws ValidationException
     */
    private function createOrderContents(array $productSorted1, array $orders, array &$productSorted2, array &$catalogGoods, array &$vendorNeeded, array &$catalogNeeded)
    {
        foreach ($productSorted1 as $index => $item) {
            if (!empty($orders[$item['vendor_id']])) {
                if (!empty($vendorNeeded[$item['vendor_id']])) {
                    unset($vendorNeeded[$item['vendor_id']]);
                }
                if (!empty($catalogNeeded[$item['cat_id']])) {
                    unset($catalogNeeded[$item['cat_id']]);
                }
                $orderId = $orders[$item['vendor_id']]['id'];
                $productId = $item['id'];
                $quantity = $item['quantity'];
                $price = $catalogGoods[$item['id']]['price'];
                $productName = $catalogGoods[$item['id']]['baseProduct']['product'];
                $units = $catalogGoods[$item['id']]['baseProduct']['units'];
                $article = $catalogGoods[$item['id']]['baseProduct']['article'];
                $this->createOrderContent($orderId, $productId, $quantity, $price, $productName, $units, $article);
                unset($catalogGoods[$item['id']]);
                unset($productSorted2[$index]);
            }
        }
    }

    /**
     * @param int   $vendorId
     * @param int   $preorderId
     * @param array $catalog
     * @param array $contents
     * @param array $catalogContents
     * @return bool
     * @throws ValidationException
     */
    private function createOrder(int $vendorId, int $preorderId, array $catalog, array $contents, array $catalogContents)
    {
        $client = $this->user->organization;
        //Создаем заказ
        $order = new Order();
        $order->client_id = $client->id;
        $order->created_by_id = $this->user->id;
        $order->vendor_id = $vendorId;
        $order->status = Order::STATUS_PREORDER;
        $order->currency_id = $catalog['currency_id'];
        $order->service_id = Registry::MC_BACKEND;
        $order->preorder_id = $preorderId;
        if (!$order->save()) {
            \Yii::error(\yii\helpers\Json::encode($order->getErrors()));
            throw new ValidationException($order->getFirstErrors());
        }

        foreach ($catalogContents as $productId => $content) {
            $orderContent = new OrderContent();
            $orderContent->order_id = $order->id;
            $orderContent->product_id = $productId;
            $orderContent->quantity = $contents[$productId]['quantity'];
            $orderContent->plan_quantity = $contents[$productId]['quantity'];
            $orderContent->initial_quantity = $contents[$productId]['quantity'];
            $orderContent->price = $content['baseProduct']['price'];
            $orderContent->plan_price = $content['baseProduct']['price'];
            $orderContent->product_name = $content['baseProduct']['product'];
            $orderContent->units = $content['baseProduct']['units'];
            $orderContent->article = $content['baseProduct']['article'];
            if (!$orderContent->save()) {
                \Yii::error(\yii\helpers\Json::encode($orderContent->getErrors()));
                throw new ValidationException($orderContent->getFirstErrors());
            }
        }
        $order->calculateTotalPrice();
        return true;
    }

    /**
     * Добавление новых продуктов в предзаказ
     *
     * @param array $preOrderContent
     * @param int   $preorderId
     * @throws \Exception
     */
    private function createPreorderContent(array $preOrderContent, int $preorderId)
    {
        $newData = [];
        foreach ($preOrderContent as $index => $product) {
            $newData[] = [
                $preorderId,
                $product['id'],
                $product['quantity'],
                \gmdate('Y-m-d H:i:s'),
                \gmdate('Y-m-d H:i:s')
            ];
        }
        try {
            \Yii::$app->db->createCommand()
                ->batchInsert(PreorderContent::tableName(),
                    ['preorder_id', 'product_id', 'plan_quantity', 'created_at', 'updated_at'],
                    $newData)
                ->execute();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Метод возвращает продукты которых нет в предзаказе
     *
     * @param array    $products
     * @param Preorder $preOrder
     * @param array    $catArray
     * @param array    $vendors
     * @return array
     * @throws BadRequestHttpException
     */
    private function getNewProducts(array $products, Preorder $preOrder, array $catArray, array $vendors)
    {
        $preorderContentToProductId = [];
        //Строим массив индексами которого будут id продуктов в предзаказе
        foreach ($preOrder->preorderContents as $index => $preorderContent) {
            $preorderContentToProductId[$preorderContent->product_id] = $index + 1;
        }
        $result = [];
        foreach ($products as $product) {
            $this->validateRequest($product, ['id', 'cat_id', 'vendor_id', 'quantity']);
            if (!(is_int($product['id']) && is_int($product['cat_id']) && is_int($product['vendor_id']))) {
                throw new BadRequestHttpException('preorder.wrong_value_type');
            }
            if (empty($vendors[$product['vendor_id']])) {
                throw new BadRequestHttpException('preorder.not_your_supplier');
            }
            if (!(is_float($product['quantity']) || is_int($product['quantity']))) {
                throw new BadRequestHttpException('preorder.wrong_value_type');
            }
            if (!in_array($product['cat_id'], $catArray)) {
                throw new BadRequestHttpException('preorder.not_your_catalog');
            }
            //Если такой продукт уже есть в предзаказе, то метод add-product возвращает ошибку
            if (!empty($preorderContentToProductId[$product['id']])) {
                throw new BadRequestHttpException('preorder.product_is_in_preorder');
            } else {
                //В противном случае записываем параметры продуктов в результирующий массив
                $result[] = $product;
            }
        }
        return $result;
    }

    /**
     * Получение массива продуктов из заданных каталогов
     *
     * @param array $productIds
     * @param array $catalogNeeded
     * @return array|\yii\db\ActiveRecord[]
     */
    private function getCatalogGoods(array $productIds, array $catalogNeeded)
    {
        return CatalogGoods::find()
            ->where([
                'base_goods_id' => $productIds,
                'cat_id'        => $catalogNeeded,
            ])
            ->with('baseProduct')
            ->groupBy('id')
            ->indexBy('base_goods_id')
            ->asArray()
            ->all();
    }

    /**
     * Получание каталогов заданных поставщиков по заданным id
     *
     * @param array $catalogNeeded
     * @return array
     */
    private function getCatalogs(array $catalogNeeded)
    {
        return Catalog::find()
            ->where(['id' => $catalogNeeded])
            ->asArray()
            ->indexBy('supp_org_id')
            ->all();
    }

    /**
     * Проверка по статусу заказа о возможности добавить новый товар в предзаказ
     *
     * @param int  $orderStatus
     * @param bool $isEDI
     * @return bool
     */
    private function canAddProduct(int $orderStatus, bool $isEDI)
    {
        $result = false;
        if ($isEDI) {
            switch ($orderStatus) {
                case Order::STATUS_PREORDER:
                    $result = true;
                    break;
                case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                case Order::STATUS_REJECTED:
                case Order::STATUS_CANCELLED:
                case Order::STATUS_PROCESSING:
                case Order::STATUS_EDI_SENT_BY_VENDOR:
                case Order::STATUS_EDI_ACCEPTANCE_FINISHED:
                case Order::STATUS_DONE:
                    $result = false;
                    break;
            }
        } else {
            switch ($orderStatus) {
                case Order::STATUS_PREORDER:
                case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                case Order::STATUS_PROCESSING:
                case Order::STATUS_DONE:
                    $result = true;
                    break;
                case Order::STATUS_REJECTED:
                case Order::STATUS_CANCELLED:
                    $result = false;
                    break;
            }
        }

        return $result;
    }

    /**
     * Добавление продуктов в предзаказ
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function addProduct(array $post)
    {
        $this->validateRequest($post, ['id', 'products']);
        if (!is_array($post['products'])) {
            throw new BadRequestHttpException('preorder.wrong_value_type');
        }
        //получаем предзаказ данного пользователя
        $preOrder = $this->findPreorder($post['id'], true);
        //Получаем массив каталогов организации
        $catArray = explode(',', $this->user->organization->getCatalogs());
        //Получаем массив поставщиков данной организации
        $vendors = $this->user->organization->getSuppliers('', false);
        //получаем продукты которых нет в заданном предзаказе и которые присутствуют в $post['products']
        $result = $this->getNewProducts($post['products'], $preOrder, $catArray, $vendors);
        //Массив id продуктов
        $productIds = ArrayHelper::map($result, 'id', 'id');;
        $productCount = count($result);
        //Массив id поставщиков
        $vendorNeeded = ArrayHelper::map($result, 'vendor_id', 'vendor_id');
        $vendorsInPreorder = Organization::find()->where(['id' => $vendorNeeded])->all();
        $vendorIsEDI = [];
        foreach ($vendorsInPreorder as $index => $item) {
            /**@var $item Organization */
            $vendorIsEDI[$item->id] = $item->isEDI();
        }
        //Массив id каталогов
        $catalogNeeded = ArrayHelper::map($result, 'cat_id', 'cat_id');
        if ($productCount > count(array_unique($productIds))) {
            //Если новые продукты повторяются, то ошибка
            throw new BadRequestHttpException('preorder.product_id_repeat');
        }
        //Получаем новые продукты из каталогов
        $catalogGoods = $this->getCatalogGoods($productIds, $catalogNeeded);
        if (empty($catalogGoods)) {
            //Если продуктов по их id и id каталогов не найдено, то метод возвращает ошибку
            throw new BadRequestHttpException('preorder.product_not_found');
        }
        foreach ($result as $index => $product) {
            //Проверяем, что полученные продукты из каталогов действительно
            //распределены по каталогам и поставщикам как на входе метода и все они
            //действительно присутствуют в каталоге
            if (empty($catalogGoods[$product['id']])) {
                throw new BadRequestHttpException('preorder.product_not_found');
            }
            if ((int)$catalogGoods[$product['id']]['cat_id'] !== $product['cat_id']) {
                throw new BadRequestHttpException('preorder.product_not_in_cat');
            }
            if ((int)$catalogGoods[$product['id']]['baseProduct']['supp_org_id'] !== $product['vendor_id']) {
                throw new BadRequestHttpException('preorder.not_supp_product');
            }
        }
        //Получаем все заказы в данном предзаказе
        $orders = $preOrder->orders;
        $canAdd = [];
        foreach ($orders as $index => $order) {
            $isEdi = $vendorIsEDI[$order->vendor_id] ?? false;
            $canAdd[] = $this->canAddProduct($order->status, $isEdi);
        }
        if (in_array(false, $canAdd)) {
            throw new BadRequestHttpException('preorder.cannot_add_product_to_some_order');
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            //Добавляем новое содержимое в предзаказ
            $this->createPreorderContent($result, $preOrder->id);
            $productSorted = $result;
            ArrayHelper::multisort($productSorted, ['vendor_id'], [SORT_ASC]);
            $productSorted1 = $productSorted2 = ArrayHelper::index($productSorted, 'id');
            //Добавляем содержимое в заказы
            $this->createOrderContents($productSorted1, $orders, $productSorted2, $catalogGoods, $vendorNeeded, $catalogNeeded);
            if (!empty($productSorted2)) {
                //Получаем массив продуктов распределённый по поставщикам
                $vendorNewOrders = ArrayHelper::index($productSorted2, 'id', 'vendor_id');
                $catalogByVendors = [];
                //Получаем массив продуктов, распределённых по поставщикам
                foreach ($vendorNewOrders as $vendorId => $vendorNewOrder) {
                    if (empty($catalogByVendors[$vendorId])) {
                        $catalogByVendors[$vendorId] = [];
                    }
                    foreach ($vendorNewOrder as $productId => $item) {
                        if (!empty($catalogGoods[$productId])) {
                            $catalogByVendors[$vendorId][$productId] = $catalogGoods[$productId];
                        }
                    }
                }
                $catalogs = $this->getCatalogs($catalogNeeded);
                //Создаём заказы
                foreach ($vendorNewOrders as $vendorId => $content) {
                    $this->createOrder(
                        $vendorId,
                        $post['id'],
                        $catalogs[$vendorId],
                        $content,
                        $catalogByVendors[$vendorId]
                    );
                }
            }
            $t->commit();
        } catch (\Throwable $e) {
            $t->rollBack();
            throw $e;
        }

        $preOrder->refresh();
        return $this->prepareModel($preOrder, true);
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function updateProduct($request)
    {
        $this->validateRequest($request, ['id']);
        $orderContent = OrderContent::findOne(['id' => $request['id']]);
        if (!$orderContent) {
            throw new BadRequestHttpException('order_content.not_found');
        }
        $order = $orderContent->order;

        $t = \Yii::$app->db->beginTransaction();
        try {
            $orderContent->quantity = $request['quantity'];
            if ($orderContent->quantity === 0) {
                $is = $this->issetProductsAnalog($orderContent->product_id);
                if (isset($is[$orderContent->product_id])) {
                    if (!$orderContent->delete()) {
                        throw new Exception('Delete false');
                    }
                }
            } else {
                if (!$orderContent->save()) {
                    throw new ValidationException($orderContent->getFirstErrors());
                }
            }
            $order->calculateTotalPrice();
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }

        return [
            'preorder' => $this->get(['id' => $order->preorder_id]),
            'order'    => (new OrderWebApi())->getOrderInfo($order)
        ];
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
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
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function orderClear($request)
    {
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
            foreach ($orderContents as $orderContent) {
                if (isset($issetAnalog[$orderContent->product_id])) {
                    $orderContent->delete();
                } else {
                    $orderContent->quantity = 0;
                    if (!$orderContent->save()) {
                        throw new ValidationException($orderContent->getFirstErrors());
                    }
                }
            }
            $order->calculateTotalPrice();
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }

        return [
            'preorder' => $this->get(['id' => $order->preorder_id]),
            'order'    => (new OrderWebApi())->getOrderInfo($order)
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

            foreach ($order->orderContent as $orderContent) {
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
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function addAnalogProduct($request)
    {
        $this->validateRequest($request, ['preorder_id', 'product_id', 'analog_id', 'quantity']);
        return $request;
    }
}
