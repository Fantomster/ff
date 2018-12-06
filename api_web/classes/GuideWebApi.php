<?php

namespace api_web\classes;

use api_web\exceptions\ValidationException;
use api_web\helpers\WebApiHelper;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\guides\Guide;
use common\models\guides\GuideProduct;
use common\models\Organization;
use common\models\search\GuideProductsSearch;
use common\models\search\GuideSearch;
use yii\base\InvalidArgumentException;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\Expression;
use yii\web\BadRequestHttpException;

/**
 * Class GuideWebApi
 *
 * @package api_web\classes
 */
class GuideWebApi extends \api_web\components\WebApi
{
    /**
     * Сюда собираем продукты, которые методом productInsert() вносим в базу
     * формат заполнения
     * array(
     *   array(
     *      'guide_id' => integer,
     *      'cbg_id' => integer,
     *      'created_at' => NOW(),
     *      'updated_at' => NOW()
     *   ), ...
     * )
     *
     * @var array
     */
    private $add_products = [];

    /**
     * Список шаблонов
     *
     * @param array $post
     * @return array
     * @throws InvalidArgumentException
     */
    public function getList(array $post)
    {
        $sort = (!empty($post['sort']) ? $post['sort'] : 'created_at');
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);
        $product_list = (isset($post['product_list']) ? $post['product_list'] : false);

        $client = $this->user->organization;
        $search = new GuideSearch();

        if (isset($post['search'])) {
            /**
             * Чистим от кривых входящих параметров
             */
            WebApiHelper::clearRequest($post['search']);
            /**
             * Фильтр по поставщику
             */
            if (isset($post['search']['vendors']) && !empty($post['search']['vendors'])) {
                if (!is_array($post['search']['vendors'])) {
                    $post['search']['vendors'] = [$post['search']['vendors']];
                }
                $search->vendors = $post['search']['vendors'];
            }
            /**
             * Фильтр по дате создания
             */
            if (isset($post['search']['create_date'])) {
                if (isset($post['search']['create_date']['start'])) {
                    $search->date_from = $post['search']['create_date']['start'];
                }

                if (isset($post['search']['create_date']['end'])) {
                    $search->date_to = $post['search']['create_date']['end'];
                }
            }

            /**
             * Фильтр по дате обновления
             */
            if (isset($post['search']['updated_date'])) {
                if (isset($post['search']['updated_date']['start'])) {
                    $search->updated_date_from = $post['search']['updated_date']['start'];
                }

                if (isset($post['search']['updated_date']['end'])) {
                    $search->updated_date_to = $post['search']['updated_date']['end'];
                }
            }

            /**
             * Фильтр по цвету
             */
            if (isset($post['search']['color'])) {
                $search->color = mb_strtoupper(ltrim(trim($post['search']['color']), '#'));
            }
        }

        $dataProvider = $search->search([], $client->id);

        //Пагинация
        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        //Сотрировка
        if (!empty($sort)) {
            $sort = str_replace('updated_date', 'updated_at', trim($sort));
            $s = new Sort(['attributes' => ['id', 'name', 'updated_at']]);
            $s->params = ['sort' => $sort];
            $dataProvider->setSort($s);
        }

        $result = [];
        if (!empty($dataProvider->models)) {
            $models = $dataProvider->models;
            /**
             * @var Guide $model
             */
            foreach ($models as $model) {
                $result[] = $this->prepareGuide($model->id, $product_list, ['limit' => 4]);
            }
        }
        $return = [
            'result'     => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
        return $return;
    }

    /**
     * Информация о шаблоне
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getInfo(array $post)
    {
        $this->validateRequest($post, ['guide_id']);

        $this->isMyGuide($post['guide_id']);

        return $this->prepareGuide($post['guide_id']);
    }

    /**
     * Список продуктов шаблона, с пагинацией и фильтрами
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getProducts(array $post)
    {
        $this->validateRequest($post, ['guide_id']);

        $this->isMyGuide($post['guide_id']);

        $sort = (isset($post['sort']) ? $post['sort'] : 'product');
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 1000);

        $client = $this->user->organization;

        $search = new GuideProductsSearch();
        if (!empty($post['search'])) {
            /**
             * Поиск по наименованию
             */
            if (!empty($post['search']['product'])) {
                $search->searchString = $post['search']['product'];
            }
            /**
             * Фильтр по поставщику
             */
            if (!empty($post['search']['vendor_id'])) {
                $search->vendor_id = $post['search']['vendor_id'];
            }
            /**
             * Фильтр по цене
             */
            if (!empty($post['search']['price'])) {
                if (!empty($post['search']['price']['from'])) {
                    $search->price_from = $post['search']['price']['from'];
                }

                if (!empty($post['search']['price']['to'])) {
                    $search->price_to = $post['search']['price']['to'];
                }
            }
        }

        $dataProvider = $search->search([], $post['guide_id'], $client->id);

        //Пагинация
        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        //Сотрировка
        if ($sort) {
            $sort = str_replace('vendor', 'name', trim($sort));
            $s = new Sort(['attributes' => ['price', 'product', 'name', 'updated_at']]);
            $s->params = ['sort' => $sort];
            $dataProvider->setSort($s);
        }

        $result = [];
        if (!empty($dataProvider->models)) {
            $models = $dataProvider->models;
            foreach ($models as $model) {
                $result[] = $this->prepareProduct($model);
            }
        }
        $return = [
            'products'   => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
        return $return;
    }

    /**
     * Создание шаблона
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function create(array $post)
    {
        $this->validateRequest($post, ['name', 'color']);

        $client = $this->user->organization;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($client->type_id === Organization::TYPE_RESTAURANT) {
                $guide = new Guide();
                $guide->client_id = $client->id;
                $guide->name = $post['name'];
                $guide->color = mb_strtoupper(ltrim(trim($post['color']), '#')) ?? 'EEEEEE';
                $guide->type = Guide::TYPE_GUIDE;
                if ($guide->validate() && $guide->save()) {
                    if (!empty($post['products'])) {
                        foreach ($post['products'] as $id) {
                            $this->addProduct($guide->id, $id);
                        }
                        $this->productInsert();
                    }
                } else {
                    throw new ValidationException($guide->getFirstErrors());
                }
                $transaction->commit();
                return $this->prepareGuide($guide->id);
            } else {
                throw new BadRequestHttpException("guide.vendor_create_denied");
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Создание шаблона из заказа
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function createFromOrder(array $post)
    {
        $this->validateRequest($post, ['order_id']);

        $order = \common\models\Order::findOne(['id' => $post['order_id'], 'client_id' => $this->user->organization->id]);
        if (empty($order)) {
            throw new BadRequestHttpException("order_not_found");
        }

        if (empty($order->orderContent)) {
            throw new BadRequestHttpException("order_content_not_found");
        }

        $request = [
            'name'     => \Yii::t('api_web', 'guide.template_name') . $order->id . ' ' . date('d/m/Y'),
            'color'    => Guide::$COLORS[rand(0, (count(Guide::$COLORS) - 1))],
            'products' => []
        ];

        foreach ($order->orderContent as $orderContent) {
            $request['products'][] = $orderContent->product_id;
        }
        $array = $this->create($request);
        return $array;
    }

    /**
     * Удалить шаблон
     *
     * @param array $params
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(array $params)
    {
        $this->validateRequest($params, ['guide_id']);
        $this->isMyGuide($params['guide_id']);
        $model = Guide::findOne($params['guide_id']);
        if ($model) {
            $model->delete();
        }
    }

    /**
     * Переименовать шаблон
     *
     * @param array $params
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function rename(array $params)
    {
        $this->validateRequest($params, ['guide_id', 'name']);

        $this->isMyGuide($params['guide_id']);

        $model = Guide::findOne($params['guide_id']);
        if ($model) {
            $model->name = $params['name'];
            if ($model->validate() && $model->save()) {
                return $this->prepareGuide($model->id);
            } else {
                throw new ValidationException($model->getFirstErrors());
            }
        }
    }

    /**
     * Меняем цвет шаблона
     *
     * @param array $params
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function changeColorGuide(array $params)
    {
        $this->validateRequest($params, ['guide_id', 'color']);

        $this->isMyGuide($params['guide_id']);

        $model = Guide::findOne($params['guide_id']);
        if ($model) {
            $model->color = mb_strtoupper(ltrim(trim($params['color']), '#'));
            if ($model->validate() && $model->save()) {
                return $this->prepareGuide($model->id);
            } else {
                throw new ValidationException($model->getFirstErrors());
            }
        }
    }

    /**
     * Добавить шаблон в корзину
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function addToCart(array $post)
    {
        $this->validateRequest($post, ['guide_id']);

        $this->isMyGuide($post['guide_id']);

        $client = $this->user->organization;
        $dataProvider = (new GuideProductsSearch())->search([], $post['guide_id'], $client->id);
        $result = $dataProvider->models;
        if (empty($result)) {
            throw new BadRequestHttpException("guide.empty_goods");
        }

        $products = [];
        foreach ($result as $product) {
            $products[] = [
                'quantity'   => $product['units'] ?? 1,
                'product_id' => $product['cbg_id'],
                'catalog_id' => $product['cat_id']
            ];
        }

        /**
         * @var $cart CartWebApi
         */
        $cart = $this->container->get('CartWebApi');
        $cart->add($products);
        return $cart->items();
    }

    /**
     * Агрегированая функция для работы с шаблоном
     *
     * @param $params
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionProductFromGuide($params)
    {
        set_time_limit(60 * 3);
        $this->validateRequest($params, ['guide_id', 'products']);
        $this->isMyGuide($params['guide_id']);

        $result = [
            'success' => 0,
            'error'   => 0
        ];

        try {
            foreach ($params['products'] as &$product) {
                if (!in_array($product['operation'], ['add', 'del'])) {
                    throw new BadRequestHttpException("guide.operation_not_found|" . $product['operation']);
                }
                //Добавляем продукт в шаблон
                if ($product['operation'] == 'add') {
                    if (Guide::findOne($params['guide_id'])->getGuideProducts()->where(['cbg_id' => $product['product_id']])->exists()) {
                        continue;
                    }
                    $this->addProduct($params['guide_id'], $product['product_id']);
                }
                //Удаление продукта из шаблона
                if ($product['operation'] == 'del') {
                    $this->operationRemoveProduct($params['guide_id'], $product['product_id']);
                }
                $result['success']++;
            }

            $this->productInsert();

            $guide = Guide::findOne($params['guide_id']);
            $guide->updated_at = new Expression('NOW()');
            $guide->save();

        } catch (\Exception $e) {
            $result['error']++;
            $result['messages'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * @param int $guide_id
     * @param     $id
     * @throws BadRequestHttpException
     */
    private function addProduct(int $guide_id, $id)
    {
        if (!is_array($id)) {
            $products_ids[] = $id;
        } else {
            $products_ids = $id;
        }

        $guide = Guide::findOne($guide_id);

        if ($guide->getProductCount() == 1000) {
            throw new BadRequestHttpException('guide.max_products|1000');
        }

        /**
         * @var Organization $client
         */
        $client = $this->user->organization;
        foreach ($products_ids as $id) {
            $product = $client->getProductIfAvailable($id);
            if ($product) {
                $newProduct = GuideProduct::findOne(['guide_id' => $guide_id, 'cbg_id' => $id]);
                if (!$newProduct) {
                    $this->add_products[] = [
                        'guide_id'   => $guide->id,
                        'cbg_id'     => $id,
                        'created_at' => new Expression('NOW()'),
                        'updated_at' => new Expression('NOW()')
                    ];
                }
            } else {
                throw new BadRequestHttpException('guide.not_add_product_in_guide|' . $id);
            }
        }
    }

    /**
     * Записать продукты в базу
     *
     * @throws \Exception
     */
    private function productInsert(): void
    {
        if (!empty($this->add_products)) {
            \Yii::$app->db->createCommand()->batchInsert(GuideProduct::tableName(), [
                'guide_id',
                'cbg_id',
                'created_at',
                'updated_at'
            ], $this->add_products)->execute();
        }
    }

    /**
     * @param $guide_id
     * @param $pid
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    private function operationRemoveProduct($guide_id, $pid)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $model = Guide::findOne($guide_id);
            if ($model) {
                $product = $model->getGuideProducts()->where(['cbg_id' => $pid])->one();
                if ($product) {
                    if ($product->delete()) {
                        $transaction->commit();
                    } else {
                        throw new ValidationException($product->getFirstErrors());
                    }
                } else {
                    throw new BadRequestHttpException("guide.product_not_found|" . $pid);
                }
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Подготавливаем шаблон к ответу
     *
     * @param       $id
     * @param bool  $product_list
     * @param array $attr
     * @return array
     */
    private function prepareGuide($id, $product_list = true, $attr = [])
    {
        $model = Guide::findOne($id);
        if ($model) {
            $return = [
                'id'            => (int)$model->id,
                'name'          => $model->name,
                'color'         => $model->color,
                'created_at'    => WebApiHelper::asDatetime($model->created_at),
                'updated_at'    => WebApiHelper::asDatetime($model->updated_at),
                'product_count' => (int)$model->productCount,
            ];

            if ($product_list === true) {
                $products = [];
                $dataProvider = (new GuideProductsSearch())->search($attr, $model->id, $this->user->organization->id);
                foreach ($dataProvider->models as $row) {
                    $products[] = $this->prepareProduct($row);
                }
                $return['products'] = $products;
            }

            return $return;
        }
    }

    /**
     * @param $row
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function prepareProduct($row)
    {
        $model = CatalogGoods::find()->where(['base_goods_id' => $row['cbg_id'], 'cat_id' => $row['cat_id']])->one();
        if (empty($model)) {
            $model = CatalogBaseGoods::find()->where(['id' => $row['cbg_id'], 'cat_id' => $row['cat_id']])->one();
        }

        $item['id'] = ($model instanceof CatalogGoods) ? (int)$model->baseProduct->id : (int)$model->id;
        $item['product'] = $model->baseProduct->product;
        $item['catalog_id'] = ((int)$model->cat_id ?? null);
        $item['category_id'] = (isset($model->category) ? (int)$model->category->id : 0);
        $item['price'] = round($row['price'] ?? 0, 2);
        $item['discount_price'] = round($model->discount ?? 0, 2);
        $item['rating'] = round($model->baseProduct->ratingStars ?? 0, 1);
        $item['supplier'] = $model->baseProduct->vendor->name;
        $item['supplier_id'] = (int)$model->baseProduct->vendor->id;
        $item['brand'] = $model->brand ?? '';
        $item['article'] = $model->baseProduct->article;
        $item['ed'] = $model->baseProduct->ed;
        $item['units'] = $model->baseProduct->units ?? 0;
        $item['currency'] = $model->catalog->currency->symbol;
        $item['currency_id'] = (int)$model->catalog->currency->id;
        $item['updated_at'] = isset($row['updated_at']) ? WebApiHelper::asDatetime($row['updated_at']) : null;
        $item['image'] = $this->container->get('MarketWebApi')->getProductImage($model->baseProduct);
        $item['in_basket'] = $this->container->get('CartWebApi')->countProductInCart($model->id);
        return $item;
    }

    /**
     * Проверка на мой шаблон
     *
     * @param $guide_id
     * @throws BadRequestHttpException
     */
    private function isMyGuide($guide_id)
    {
        $client = $this->user->organization;
        $model = Guide::findOne($guide_id);

        if (empty($model)) {
            throw new BadRequestHttpException('guide.not_found');
        }

        if ($model->client_id != $client->id) {
            throw new BadRequestHttpException('guide.access_denied');
        }
    }
}