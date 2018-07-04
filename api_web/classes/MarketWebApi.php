<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\helpers\WebApiHelper;
use common\models\CatalogGoods;
use common\models\Category;
use common\models\MpCategory;
use common\models\Organization;
use common\models\DeliveryRegions;
use common\models\CatalogBaseGoods;
use common\models\RelationSuppRest;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;

/**
 * Class MarketWebApi
 * @package api_web\classes
 */
class MarketWebApi extends WebApi
{
    /**
     * Список доступных для заказа продуктов на маркете
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function products($post)
    {
        $sort = (isset($post['sort']) ? $post['sort'] : null);
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $currentUser = $this->user;

        $result = CatalogBaseGoods::find()
            ->joinWith(['vendor', 'category'])
            ->where([
                'organization.white_list' => Organization::WHITE_LIST_ON,
                'market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                'status' => CatalogBaseGoods::STATUS_ON,
                'deleted' => CatalogBaseGoods::DELETED_OFF])
            ->andWhere('category_id is not null')
            ->limit($pageSize)
            ->offset($pageSize * ($page - 1));


        if (!\Yii::$app->user->isGuest) {
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $relation = RelationSuppRest::find()
                    ->select('supp_org_id as id,supp_org_id as supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->asArray()
                    ->all();
                foreach ($relation as $row) {
                    $relationSuppliers[] = $row['id'];
                }
            }
        }


        if (!empty(\Yii::$app->session->get('city')) || !empty(\Yii::$app->session->get('region'))) {
            $supplierRegion = DeliveryRegions::getSuppRegion(\Yii::$app->session->get('city'), \Yii::$app->session->get('region'));
            if (!empty($supplierRegion)) {
                if (!empty($relationSuppliers)) {
                    $supplierRegion = \array_udiff($supplierRegion, $relationSuppliers, function ($a, $b) {
                        return $a - $b;
                    });
                }
                $result->andWhere(['in', 'supp_org_id', $supplierRegion]);
            } else {
                if (!empty($relationSuppliers)) {
                    $result->andWhere(['not in', 'supp_org_id', $relationSuppliers]);
                }
            }
        }


        //Условия поиска
        if (isset($post['search'])) {
            foreach ($post['search'] as $key => $value) {

                if (empty($value)) {
                    continue;
                }

                if ($key == 'supplier_id') {
                    $key = 'supp_org_id';
                    if (!empty($value)) {
                        if (is_array($value)) {
                            $supp_orgs = [];
                            foreach ($value as $supp_org_id) {
                                $supp_orgs[] = (int)$supp_org_id;
                            }
                            $value = implode(', ', $supp_orgs);
                        } else {
                            $value = (int)$value;
                        }
                        $result->andWhere("$key IN ($value)");
                    }
                }

                if (in_array($key, ['category_id'])) {
                    if (!empty($value)) {
                        if (is_array($value)) {
                            $categories = [];
                            foreach ($value as $category) {
                                $categories[] = (int)$category;
                            }
                            $value = implode(', ', $categories);
                        } else {
                            $value = (int)$value;
                        }
                        $result->andWhere("$key IN ($value) OR parent IN ($value)");
                    }
                }

                if (in_array($key, ['product'])) {
                    $result->andFilterWhere(['like', $key, $value]);
                }

                if (in_array($key, ['price'])) {
                    if (is_array($value)) {
                        if (!empty($value['from'])) {
                            $result->andWhere('price >= :price_start', [':price_start' => $value['from']]);
                        }
                        if (!empty($value['to'])) {
                            $result->andWhere('price <= :price_end', [':price_end' => $value['to']]);
                        }
                    } else {
                        throw new BadRequestHttpException('Filter "price" not array');
                    }
                }
            }
        }
        //Готовим ответ
        $return = ['headers' => [],
            'products' => [],
            'pagination' => ['page' => $page,
                'page_size' => $pageSize,
                'total_page' => ceil($result->count() / $pageSize)]];
        //Сортировка
        if ($sort) {
            $sort = str_replace('supplier_id', 'organization.id', $sort);
            $sort = str_replace('supplier', 'organization.name', $sort);
            $return['sort'] = $sort;
            $order = 'ASC';
            if (preg_match('#^-(.+?)$#', $sort, $out)) {
                $sort = $out[1];
                $order = 'DESC';
            }

            $result->orderBy($sort . ' ' . $order);
        } else {
            $result->orderBy(['rating' => SORT_DESC]);
        }
//Результат
        $result = $result->all();
        foreach ($result as $model) {
            $return['products'][] = $this->prepareProduct($model);
        }
        /**
         * @var CatalogBaseGoods $model
         */
        if (isset($return['products'][0])) {
            foreach (array_keys($return['products'][0]) as $key) {
                $return['headers'][$key] = $model->getAttributeLabel($key);
            }
        }
        return $return;
    }

    /**
     * Список доступных категорий на маркете
     * @return array
     */
    public function categories()
    {
        $return = [];
        $categories = MpCategory::find()->where('parent is null')->all();
        \Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
        foreach ($categories as $model) {
            $category = [
                'id' => $model->id,
                'name' => $model->name,
                'image' => $this->getCategoryImage($model->id),
                'subcategories' => []
            ];
            $all_child = $model->child;
            if (!empty($all_child)) {
                foreach ($all_child as $child) {
                    //Картинка категории
                    $image = $this->getCategoryImage($child->id);
                    //Если нет картинки, ставим картинку родителя
                    if (strstr($image, 'product_placeholder') !== false) {
                        $image = $this->getCategoryImage($model->id);
                    }
                    $category['subcategories'][] = [
                        'id' => $child->id,
                        'name' => $child->name,
                        'image' => $image
                    ];
                }
            }
            $return[] = $category;
        }
        return $return;
    }

    /**
     * @param $post
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function product($post)
    {
        if (isset($post['id'])) {

            $model = CatalogBaseGoods::findOne(['id' => $post['id']]);
            if (empty($model)) {
                throw new BadRequestHttpException('Нет продукта с таким id');
            }

            $currentUser = $this->user;
            if (!\Yii::$app->user->isGuest) {
                $client = $currentUser->organization;
                if ($client->type_id == Organization::TYPE_RESTAURANT) {
                    $relation = RelationSuppRest::find()
                        ->select('supp_org_id as id,supp_org_id as supp_org_id')
                        ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                        ->asArray()
                        ->all();
                    foreach ($relation as $row) {
                        $relationSuppliers[] = $row['id'];
                    }
                    if (!empty($relationSuppliers)) {
                        if (in_array($model->supp_org_id, $relationSuppliers)) {
                            throw new BadRequestHttpException('Нет доступа к продукту');
                        }
                    }
                }
            }

            return $this->prepareProduct($model);
        } else {
            throw new BadRequestHttpException('Пустое значение id');
        }
    }

    /**
     * Список организаций на маркете
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public
    function organizations($post)
    {
        $sort = (isset($post['sort']) ? $post['sort'] : null);
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $currentUser = $this->user;

        $result = Organization::find()
            ->where(['white_list' => Organization::WHITE_LIST_ON])
            ->limit($pageSize)
            ->offset($pageSize * ($page - 1));

        switch ($post['type_id']) {
            case Organization::TYPE_SUPPLIER:
                $result->andWhere(['type_id' => Organization::TYPE_SUPPLIER]);
                break;
            case Organization::TYPE_RESTAURANT:
                $result->andWhere(['type_id' => Organization::TYPE_RESTAURANT]);
                break;
            default:
                throw new BadRequestHttpException('Тип организаций не указан, или указан неверно');
        }

        if (!\Yii::$app->user->isGuest) {
            $client = $currentUser->organization;
            if ($client->type_id == Organization::TYPE_RESTAURANT) {
                $relation = RelationSuppRest::find()
                    ->select('supp_org_id as id,supp_org_id as supp_org_id')
                    ->where(['rest_org_id' => $client->id, 'invite' => RelationSuppRest::INVITE_ON])
                    ->asArray()
                    ->all();
                foreach ($relation as $row) {
                    $relationSuppliers[] = $row['id'];
                }
            }
        }

        if (!empty(\Yii::$app->session->get('city')) || !empty(\Yii::$app->session->get('region'))) {
            $supplierRegion = DeliveryRegions::getSuppRegion(\Yii::$app->session->get('city'), \Yii::$app->session->get('region'));
            if (!empty($supplierRegion)) {
                if (!empty($relationSuppliers)) {
                    $supplierRegion = \array_udiff($supplierRegion, $relationSuppliers, function ($a, $b) {
                        return $a - $b;
                    });
                }
                $result->andWhere(['in', 'id', $supplierRegion]);
            } else {
                if (!empty($relationSuppliers)) {
                    $result->andWhere(['not in', 'id', $relationSuppliers]);
                }
            }
        }

        //Условия поиска
        if (isset($post['search'])) {
            foreach ($post['search'] as $key => $value) {
                if (is_numeric($value)) {
                    $result->andFilterWhere([$key => $value]);
                } else {
                    $result->andFilterWhere(['like', $key, $value]);
                }
            }
        }
        //Готовим ответ
        $return = [
            'headers' => [],
            'organizations' => [],
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total_page' => ceil($result->count() / $pageSize)
            ]
        ];
        //Сортировка
        if ($sort) {
            $return['sort'] = $sort;
            $order = 'ASC';
            if (preg_match('#^-(.+?)$#', $sort, $out)) {
                $sort = $out[1];
                $order = 'DESC';
            }
            $result->orderBy($sort . ' ' . $order);
        } else {
            $result->orderBy(['rating' => SORT_DESC]);
        }
        //Результат
        $result = $result->all();
        foreach ($result as $model) {
            $return['organizations'][] = WebApiHelper::prepareOrganization($model);
        }
        /**
         * @var CatalogBaseGoods $model
         */
        if (isset($return['organizations'][0])) {
            foreach (array_keys($return['organizations'][0]) as $key) {
                $return['headers'][$key] = $model->getAttributeLabel($key);
            }
        }
        return $return;
    }

    /**
     * Собираем массив для отдачи, из модели
     * @param CatalogBaseGoods $model
     * @return mixed
     */
    public
    function prepareProduct($model)
    {
        $catalogGoodsModel = CatalogGoods::findOne(['base_goods_id' => $model->id, 'cat_id' => $model->cat_id]);

        $price = (isset($catalogGoodsModel->price) ? $catalogGoodsModel->price : $model->price);
        $discount_price = (isset($catalogGoodsModel->discountPrice) ? $catalogGoodsModel->discountPrice : $model->price);
        $catalog_id = (isset($catalogGoodsModel->catalog) ? $catalogGoodsModel->catalog->id : $model->catalog->id);

        if ($price == $discount_price) {
            $discount_price = 0;
        }

        $item['id'] = (int)$model->id;
        $item['product'] = $model->product;
        $item['catalog_id'] = ((int)$catalog_id ?? null);
        $item['category_id'] = (isset($model->category) ? (int)$model->category->id : 0);
        $item['price'] = round($price, 2);
        $item['discount_price'] = round($discount_price, 2);
        $item['rating'] = round($model->ratingStars, 1);
        $item['supplier'] = $model->vendor->name;
        $item['supplier_id'] = (int)$model->vendor->id;
        $item['brand'] = $model->brand ?? '';
        $item['article'] = $model->article;
        $item['ed'] = $model->ed;
        $item['units'] = $model->units ?? 0;
        $item['currency'] = $model->catalog->currency->symbol;
        $item['currency_id'] = (int)$model->catalog->currency->id;
        $item['image'] = $this->getProductImage($model);
        $item['in_basket'] = $this->container->get('CartWebApi')->countProductInCart($model->id);
        return $item;
    }

    /**
     * Определяем ссылку на картинку товара
     * @param CatalogBaseGoods $model
     * @return string
     */
    public
    function getProductImage($model)
    {
        $url = $model->getImageUrl();
        if (strstr($url, 'amazon') === false && strstr($url, 'data:image') === false) {
            return \Yii::$app->params['web'] . preg_replace('#http(.+?)\/\/(.+?)\/(.+?)#', '$3', $url);
        } else {
            return \Yii::$app->params['web'].'site/image-base?id='.$model->id.'&type=product';
        }
    }

    /**
     * Картинка категории
     * @param $id
     * @return string
     */
    public function getCategoryImage($id)
    {
        if (file_exists(\Yii::getAlias('@market') . '/web/fmarket/images/image-category/' . $id . ".jpg")) {
            return Url::to('@market_web/fmarket/images/image-category/' . $id . ".jpg", true);
        } else {
            return Url::to('@market_web/fmarket/images/product_placeholder.jpg', true);
        }
    }
}