<?php
/**
 * Date: 04.02.2019
 * Author: Mike N.
 * Time: 14:35
 */

namespace api_web\classes;

use api_web\{
    helpers\WebApiHelper,
    exceptions\ValidationException,
    components\WebApi,
    components\Notice,
    helpers\CurrencyHelper};
use common\models\{Catalog,
    CatalogGoods,
    Order,
    OrderContent,
    OrderStatus,
    Preorder,
    Cart,
    Organization,
    PreorderContent,
    Profile,
    RelationSuppRest};
use function GuzzleHttp\Promise\all;
use yii\data\{
    ArrayDataProvider,
    Pagination
};
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
        if (!$preOrder->save(true)) {
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
                    if (!$preOrderContent->save(true)) {
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
     * @throws BadRequestHttpException
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
            if (!empty($request['search']['status'])) {
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
     * @throws BadRequestHttpException
     */
    private function productsInfo(Preorder $preOrder)
    {
        $orders = $preOrder->orders;
        $products = [];
        $contents = $preOrder->getPreorderContents()->asArray()->all();
        $planQuantity = ArrayHelper::map($contents, 'product_id', 'plan_quantity');
        foreach ($orders as $order) {
            $orderContent = $order->orderContent;
            foreach ($orderContent as $item) {
                if (empty($planQuantity[$item->product_id])) {
                    throw new BadRequestHttpException('preorder.wrong_preorder');
                }
                $products[] = [
                    'id'            => $item->product_id,
                    'name'          => $item->product_name,
                    'article'       => $item->article,
                    'plan_quantity' => $planQuantity[$item->product_id],
                    'quantity'      => $item->quantity,
                    'sum'           => CurrencyHelper::asDecimal($item->quantity * $item->price),
                    'isset_analog'  => false,
                ];
            }
        }
        return $products;
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
        foreach ($orders as $order) {
            $order->status = OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
            if (!$order->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
            $vendor = $order->vendor;
            //Емайл и смс о новом заказе
            Notice::init('Order')->sendEmailAndSmsOrderCreated($this->user->organization, $order);
            try {
                //Сообщение в очередь поставщику, что есть новый заказ
                Notice::init('Order')->sendOrderToTurnVendor($vendor);
                //Сообщение в очередь, Изменение количества товара в корзине
                Notice::init('Order')->sendOrderToTurnClient($this->user);
            } catch (\Exception $e) {
                \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        }
        return ['result' => true];
    }

    /**
     * Подготовка модели к выдаче фронту
     *
     * @param Preorder $model
     * @param bool     $products
     * @return array
     * @throws BadRequestHttpException
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
                'products' => $model->getPreorderContents()->count(),
                'orders'   => $model->getOrders()->count(),
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
     * Находим предзаказ по id
     *
     * @param int  $id
     * @param bool $withContent
     * @return array|Preorder|\yii\db\ActiveRecord[]|null
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
                ->asArray()
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
        $order->service_id = 9;
        $order->preorder_id = $preorderId;
        if (!$order->validate() || !$order->save()) {
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
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function addProduct(array $post)
    {
        $this->validateRequest($post, ['id', 'products']);
        $preOrder = $this->findPreorder($post['id'], true);
        $productsInPreorder = ArrayHelper::map($preOrder['preorderContents'], 'id', 'product_id');
        if (!is_array($post['products'])) {
            throw new BadRequestHttpException('Неправильное значение параметра.');
        }
        $catArray = explode(',', $this->user->organization->getCatalogs());
        $vendors = $this->user->organization->getSuppliers();
        unset($vendors[0]);
        $productIds = [];
        $productCount = count($post['products']);
        $vendorNeeded = [];
        $catalogNeeded = [];
        foreach ($post['products'] as $product) {
            $this->validateRequest($product, ['id', 'cat_id', 'vendor_id', 'quantity']);
            if (!(is_int($product['id']) && is_int($product['cat_id']) && is_int($product['vendor_id']))) {
                throw new BadRequestHttpException('preorder.wrong_value_type');
            }
            $productIds[] = $product['id'];
            if (in_array($product['id'], $productsInPreorder)) {
                throw new BadRequestHttpException('preorder.product_already_exist_in_preorder');
            }
            if (empty($vendors[$product['vendor_id']])) {
                throw new BadRequestHttpException('preorder.not_your_supplier');
            }
            if (empty($vendorNeeded[$product['vendor_id']])) {
                $vendorNeeded[$product['vendor_id']] = $product['vendor_id'];
            }
            if (!(is_float($product['quantity']) || is_int($product['quantity']))) {
                throw new BadRequestHttpException('preorder.wrong_value_type');
            }
            if (!in_array($product['cat_id'], $catArray)) {
                throw new BadRequestHttpException('preorder.not_your_catalog');
            }
            if (empty($catalogNeeded[$product['cat_id']])) {
                $catalogNeeded[$product['cat_id']] = $product['cat_id'];
            }
        }

        if ($productCount > count(array_unique($productIds))) {
            throw new BadRequestHttpException('preorder.product_id_repeat');
        }

        $catalogGoods = CatalogGoods::find()
            ->where([
                'base_goods_id' => $productIds,
                'cat_id'        => $catalogNeeded,
            ])
            ->with('baseProduct')
            ->groupBy('id')
            ->indexBy('base_goods_id')
            ->asArray()
            ->all();

        if (empty($catalogGoods)) {
            throw new BadRequestHttpException('preorder.product_not_found');
        }

        foreach ($post['products'] as $index => $product) {
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

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($post['products'] as $index => $product) {
                $preOrderContent = new PreorderContent();
                $preOrderContent->preorder_id = $post['id'];
                $preOrderContent->product_id = $product['id'];
                $preOrderContent->plan_quantity = $product['quantity'];
                if (!$preOrderContent->save()) {
                    throw new ValidationException($preOrderContent->getFirstErrors());
                }
            }

            $orders = Order::find()
                ->where(['preorder_id' => $post['id']])
                ->with('orderContent')
                ->orderBy(['vendor_id' => SORT_ASC])
                ->indexBy('vendor_id')
                ->asArray()
                ->all();

            $productSorted = $post['products'];
            ArrayHelper::multisort($productSorted, ['vendor_id'], [SORT_ASC]);
            $productSorted1 = $productSorted2 = ArrayHelper::index($productSorted, 'id');

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

            if (empty($productSorted2)) {
                $changedPreOrder = Preorder::findOne(['id' => $post['id']]);
                return $this->prepareModel($changedPreOrder, true);
            }

            $vendorNewOrders = ArrayHelper::index($productSorted2, 'id', 'vendor_id');

            $catalogByVendors = [];

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

            $currency = Catalog::find()
                ->where(['id' => $catalogNeeded, 'supp_org_id' => $vendorNeeded])
                ->asArray()
                ->indexBy('supp_org_id')
                ->all();

            foreach ($vendorNewOrders as $vendorId => $content) {
                $this->createOrder($vendorId, $post['id'], $currency[$vendorId], $content, $catalogByVendors[$vendorId]);
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $changedPreOrder = Preorder::findOne(['id' => $post['id']]);
        return $this->prepareModel($changedPreOrder, true);
    }
}
