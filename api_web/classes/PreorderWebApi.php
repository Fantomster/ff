<?php
/**
 * Date: 04.02.2019
 * Author: Mike N.
 * Time: 14:35
 */

namespace api_web\classes;

use api_web\ {
    helpers\WebApiHelper,
    exceptions\ValidationException,
    components\WebApi
};
use common\models\{Order, Preorder, Cart, Organization, PreorderContent, Profile};
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
                    'sum'           => number_format($item->quantity * $item->price, 2),
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

    /**
     * Подготовка модели к выдаче фронту
     *
     * @param Preorder $model
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
}
