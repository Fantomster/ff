<?php

namespace api_web\classes;

use api_web\exceptions\ValidationException;
use common\models\CatalogBaseGoods;
use common\models\guides\Guide;
use common\models\guides\GuideProduct;
use common\models\Organization;
use common\models\search\GuideProductsSearch;
use common\models\search\GuideSearch;
use yii\data\Pagination;
use yii\data\Sort;
use yii\web\BadRequestHttpException;

/**
 * Class GuideWebApi
 * @package api_web\classes
 */
class GuideWebApi extends \api_web\components\WebApi
{
    /**
     * Список шаблонов
     * @param array $post
     * @return array
     */
    public function getList(array $post)
    {
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);
        $product_list = (isset($post['product_list']) ? $post['product_list'] : false);

        $client = $this->user->organization;
        $search = new GuideSearch();

        if (isset($post['search'])) {
            /**
             * Фильтр по поставщику
             */
            if (isset($post['search']['vendor_id'])) {
                $search->vendor_id = (int)$post['search']['vendor_id'];
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
             * Фильтр по цвету
             */
            if (isset($post['search']['color'])) {
                $search->color = mb_strtoupper(ltrim(trim($post['search']['color']), '#'));
            }
        }

        $dataProvider = $search->search(null, $client->id);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        if (!empty($dataProvider->models)) {
            $models = $dataProvider->models;
            /**
             * @var $model Guide
             */
            foreach ($models as $model) {
                $result[] = $this->prepareGuide($model->id, $product_list);
            }
        }
        $return = [
            'result' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
        return $return;
    }

    /**
     * Информация о шаблоне
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getInfo(array $post)
    {
        if (empty($post['guide_id'])) {
            throw new BadRequestHttpException("ERROR: Empty guide_id");
        }

        $this->isMyGuide($post['guide_id']);

        return $this->prepareGuide($post['guide_id']);
    }

    /**
     * Список продуктов шаблона, с пагинацией и фильтрами
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getProducts(array $post)
    {
        if (empty($post['guide_id'])) {
            throw new BadRequestHttpException("ERROR: Empty guide_id");
        }

        $this->isMyGuide($post['guide_id']);

        $sort = (isset($post['sort']) ? $post['sort'] : 'product');
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $client = $this->user->organization;

        $search = new GuideProductsSearch();
        if (isset($post['search'])) {
            /**
             * Поиск по наименованию
             */
            if (isset($post['search']['product'])) {
                $search->searchString = $post['search']['product'];
            }
            /**
             * Фильтр по поставщику
             */
            if (isset($post['search']['vendor_id'])) {
                $search->vendor_id = (int)$post['search']['vendor_id'];
            }
            /**
             * Фильтр по цене
             */
            if (isset($post['search']['price'])) {
                if (isset($post['search']['price']['start'])) {
                    $search->price_from = $post['search']['price']['start'];
                }

                if (isset($post['search']['price']['end'])) {
                    $search->price_to = $post['search']['price']['end'];
                }
            }
        }

        $dataProvider = $search->search([], $post['guide_id'], $client->id);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        if ($sort) {
            $direction = (strstr($sort, '-') === false ? SORT_ASC : SORT_DESC);
            $field = str_replace('-', '', $sort);
            if ($field === 'vendor') {
                $field = 'name';
                $direction = SORT_ASC;
            }

            $sorter = new Sort([
                'attributes' => [
                    'price',
                    'product',
                    'name'
                ],
                'defaultOrder' => [
                    $field => $direction
                ]
            ]);
            $dataProvider->setSort($sorter);
        }

        $result = [];
        if (!empty($dataProvider->models)) {
            $models = $dataProvider->models;
            foreach ($models as $model) {
                $baseModel = CatalogBaseGoods::findOne($model['cbg_id']);
                $result[] = $this->prepareProduct($baseModel);
            }
        }
        $return = [
            'products' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
        return $return;
    }

    /**
     * Создание шаблона
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function create(array $post)
    {
        if (empty($post['name'])) {
            throw new BadRequestHttpException("ERROR: Empty name");
        }
        if (empty($post['color'])) {
            throw new BadRequestHttpException("ERROR: Empty color");
        }

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
                    }
                } else {
                    throw new ValidationException($guide->getFirstErrors());
                }
                $transaction->commit();
                return $this->prepareGuide($guide->id);
            } else {
                throw new BadRequestHttpException("Создание шаблона, доступно только для Ресторана.");
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Удалить шаблон
     * @param array $params
     * @throws BadRequestHttpException
     */
    public function delete(array $params)
    {
        if (empty($params['guide_id'])) {
            throw new BadRequestHttpException("ERROR: Empty guide_id");
        }

        $this->isMyGuide($params['guide_id']);

        $model = Guide::findOne($params['guide_id']);
        if ($model) {
            $model->delete();
        }
    }

    /**
     * Переименовать шаблон
     * @param array $params
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function rename(array $params)
    {
        if (empty($params['guide_id'])) {
            throw new BadRequestHttpException("ERROR: Empty guide_id");
        }
        if (empty($params['name'])) {
            throw new BadRequestHttpException("ERROR: Empty name");
        }

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
     * @param array $params
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function changeColorGuide(array $params)
    {
        if (empty($params['guide_id'])) {
            throw new BadRequestHttpException("ERROR: Empty guide_id");
        }
        if (empty($params['color'])) {
            throw new BadRequestHttpException("ERROR: Empty color");
        }

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
     * Добавить продукт/продукты в шаблон
     * @param array $params
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function addProductToGuide(array $params)
    {
        if (empty($params['guide_id'])) {
            throw new BadRequestHttpException("ERROR: Empty guide_id");
        }
        if (empty($params['product_id'])) {
            throw new BadRequestHttpException("ERROR: Empty product_id");
        }

        $this->isMyGuide($params['guide_id']);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->addProduct($params['guide_id'], $params['product_id']);
            $transaction->commit();
            return $this->prepareGuide($params['guide_id']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    }

    /**
     * Удалить продукт из шаблона
     * @param array $params
     * @return array
     * @throws BadRequestHttpException
     */
    public function removeProductFromGuide(array $params)
    {
        if (empty($params['guide_id'])) {
            throw new BadRequestHttpException("ERROR: Empty guide_id");
        }
        if (empty($params['product_id'])) {
            throw new BadRequestHttpException("ERROR: Empty product_id");
        }

        $this->isMyGuide($params['guide_id']);

        $model = Guide::findOne($params['guide_id']);
        if ($model) {
            $product = $model->getGuideProducts()->where(['cbg_id' => $params['product_id']])->one();
            if ($product) {
                $product->delete();
            }
            return $this->prepareGuide($model->id);
        }
    }

    /**
     * Добавить шаблон в корзину
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function addToCart(array $post)
    {

        if (empty($post['guide_id'])) {
            throw new BadRequestHttpException("ERROR: Empty guide_id");
        }

        $this->isMyGuide($post['guide_id']);

        $client = $this->user->organization;
        $dataProvider = (new GuideProductsSearch())->search([], $post['guide_id'], $client->id);
        $result = $dataProvider->models;
        if (empty($result)) {
            throw new BadRequestHttpException("Нет товаров в шаблоне.");
        }

        $products = [];

        foreach ($result as $product) {
            $products[] = [
                'quantity' => $product['units'] ?? 1,
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
     * Подготавливаем шаблон к ответу
     * @param $id
     * @return array
     */
    private function prepareGuide($id, $product_list = true)
    {
        $model = Guide::findOne($id);
        if ($model) {

            $return = [
                'id' => $model->id,
                'name' => $model->name,
                'color' => $model->color,
                'created_at' => \Yii::$app->formatter->asDate($model->created_at),
                'product_count' => $model->productCount,
            ];

            if ($product_list === true) {
                $products = [];
                foreach ($model->guideProducts as $product) {
                    $products[] = $this->prepareProduct($product->baseProduct);
                }
                $return['products'] = $products;
            }

            return $return;
        }
    }

    /**
     * @param CatalogBaseGoods $baseProductModel
     * @return mixed
     */
    private function prepareProduct(CatalogBaseGoods $baseProductModel)
    {
        return $this->container->get('MarketWebApi')->prepareProduct($baseProductModel);
    }

    /**
     * Проверка на мой шаблон
     * @param $guide_id
     * @throws BadRequestHttpException
     */
    private function isMyGuide($guide_id)
    {
        $client = $this->user->organization;
        $model = Guide::findOne($guide_id);

        if (empty($model)) {
            throw new BadRequestHttpException('Шаблон не найден.');
        }

        if ($model->client_id != $client->id) {
            throw new BadRequestHttpException('Доступ закрыт!');
        }
    }

    /**
     * @param int $guide_id
     * @param $id
     * @throws BadRequestHttpException
     */
    private function addProduct(int $guide_id, $id)
    {
        if (!is_array($id)) {
            $products_ids[] = $id;
        } else {
            $products_ids = $id;
        }
        /**
         * @var $client Organization
         */
        $client = $this->user->organization;
        foreach ($products_ids as $id) {
            $product = $client->getProductIfAvailable($id);
            if ($product) {
                $newProduct = GuideProduct::findOne(['guide_id' => $guide_id, 'cbg_id' => $id]);
                if (!$newProduct) {
                    $newProduct = new GuideProduct();
                    $newProduct->guide_id = $guide_id;
                    $newProduct->cbg_id = $id;
                    $newProduct->save();
                }
            } else {
                throw new BadRequestHttpException('Вы не можете добавить этот товар в шаблон: ' . $id);
            }
        }
    }
}