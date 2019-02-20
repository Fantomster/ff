<?php
/**
 * Date: 13.02.2019
 * Author: Mike N.
 * Time: 15:19
 */

namespace api_web\classes;

use api_web\helpers\CurrencyHelper;
use api_web\helpers\WebApiHelper;
use common\models\{Catalog, CatalogBaseGoods, CatalogGoods, Organization, RelationSuppRest};
use api_web\exceptions\ValidationException;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;

class LazyVendorPriceWebApi extends LazyVendorWebApi
{
    private $marketWebApi;

    public function __construct()
    {
        $this->marketWebApi = new MarketWebApi();
        parent::__construct();
    }

    /**
     * Содержимое каталога для ленивого поставщика
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function get($request)
    {
        $this->validateRequest($request, ['vendor_id']);

        $page = $request['pagination']['page'] ?? 1;
        $pageSize = $request['pagination']['page_size'] ?? 12;
        $result = [];

        $vendor = $this->getVendor($request['vendor_id']);
        /** @var Catalog $catalog */
        $catalog = $this->getCatalog($vendor->id);
        $goods = $catalog->catalogGoods;

        if (!empty($goods)) {
            $dataProvider = new ArrayDataProvider([
                'allModels' => $goods
            ]);
            $pagination = new Pagination();
            $pagination->setPage($page - 1);
            $pagination->setPageSize($pageSize);
            $dataProvider->setPagination($pagination);
            if (!empty($dataProvider->models)) {
                foreach (WebApiHelper::generator($dataProvider->models) as $model) {
                    $result[] = $this->preparePriceRow($model);
                }
            }
            $page = ($dataProvider->pagination->page + 1);
            $pageSize = $dataProvider->pagination->pageSize;
            $totalPage = ceil($dataProvider->totalCount / $pageSize);
        }

        $return = [
            'catalog'    => [
                'id'          => $catalog->id,
                'name'        => $catalog->name,
                'vendor_name' => $catalog->vendor->name
            ],
            'items'      => $result,
            'pagination' => [
                'page'       => $page,
                'page_size'  => $pageSize,
                'total_page' => $totalPage ?? 0
            ]
        ];

        return $return;
    }

    /**
     * Получить Catalog::class
     *
     * @param $vendor_id
     * @return \common\models\Catalog
     * @throws BadRequestHttpException
     */
    private function getCatalog($vendor_id)
    {
        $model = RelationSuppRest::findOne([
            'supp_org_id' => $vendor_id,
            'rest_org_id' => $this->user->organization_id,
            'invite'      => RelationSuppRest::INVITE_ON,
        ]);
        if (empty($model)) {
            throw new BadRequestHttpException('catalog.not_found');
        }
        return $model->catalog;
    }

    /**
     * Подготовка строки каталога
     *
     * @param              $model
     * @return array
     */
    private function preparePriceRow(CatalogGoods $model)
    {
        $result = [
            'article'  => $model->baseProduct->article,
            'name'     => $model->baseProduct->product,
            'category' => null,
            'price'    => CurrencyHelper::asDecimal($model->discountPrice ?? 0),
            'ed'       => $model->baseProduct->ed ?? "",
            'units'    => $model->baseProduct->units,
            'status'   => $model->baseProduct->status,
            'picture'  => $model->baseProduct->image ? $this->marketWebApi->getProductImage($model->baseProduct) : null,
            'attr'     => [
                'cg_id'  => $model->id,
                'cbg_id' => $model->baseProduct->id,
            ]
        ];

        if ($model->baseProduct->category) {
            $result['category'] = [
                'id'   => $model->baseProduct->category->id,
                'name' => $model->baseProduct->category->name,
            ];
        }

        return $result;
    }

    /**
     * Удаление индивидуального каталога ленивого поставщика
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function deletePriceList($post)
    {
        $this->validateRequest($post, ['vendor_id']);
        if (!is_int($post['vendor_id'])) {
            throw new BadRequestHttpException('catalog.wrong_value');
        }
        $org = Organization::find()
            ->where([
                'id'      => $post['vendor_id'],
                'type_id' => Organization::TYPE_LAZY_VENDOR
            ])->one();
        if (!empty($org)) {
            throw new BadRequestHttpException('catalog.not_lazy_vendor');
        }
        $catId = $this->user->organization->getCatalogs($post['vendor_id']);
        if ($catId < 0) {
            throw new BadRequestHttpException('catalog.not_exist');
        }
        /**@var $catalog Catalog */
        $catalog = Catalog::find()
            ->where([
                'id'          => $catId,
                'type'        => Catalog::CATALOG,
                'supp_org_id' => $post['vendor_id'],
                'status'      => Catalog::STATUS_ON
            ])->one();
        if (empty($catalog)) {
            throw new BadRequestHttpException('catalog.not_exist');
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            CatalogGoods::deleteAll("cat_id = $catId");
            $catalog->status = Catalog::STATUS_OFF;
            $catalog->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return ['result' => true];
    }

    /**
     * Изменяет статус продукта
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function changeProductStatus($post)
    {
        $this->validateRequest($post, ['vendor_id', 'product_id']);
        if (!is_int($post['vendor_id']) || !is_int($post['product_id'])) {
            throw new BadRequestHttpException('catalog.wrong_value');
        }
        $catalog = $this->getCatalog($post['vendor_id']);
        $catId = $catalog->id;
        $product = CatalogGoods::findOne(['cat_id' => $catId, 'base_goods_id' => $post['product_id']]);
        if (empty($product)) {
            throw new BadRequestHttpException('catalog.no_such_product');
        }
        $productBase = CatalogBaseGoods::findOne(['id' => $post['product_id']]);
        $productBase->status = $productBase->status ? 0 : 1;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $productBase->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return ['status' => $productBase->status];
    }

    /**
     * Добавление продукта в каталог ленивого поставщика
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function addProduct(array $post)
    {
        $this->validateRequest($post, ['vendor_id', 'name', 'price', 'ed']);
        if (!is_int($post['vendor_id'])) {
            throw new BadRequestHttpException('catalog.wrong_value');
        }
        $catalog = $this->getCatalog($post['vendor_id']);
        $catId = $catalog->id;
        if (!empty($post['article'])) {
            $product = CatalogBaseGoods::find()->where(
                "article=:article AND product=:name AND cat_id=:cat_id", [
                ':article' => $post['article'],
                ':name'    => $post['name'],
                ':cat_id'  => $catId,
            ])->one();
        } else {
            $product = CatalogBaseGoods::find()->where(
                "product=:name AND cat_id=:cat_id", [
                ':name'   => $post['name'],
                ':cat_id' => $catId,
            ])->one();
        }
        if (!empty($product)) {
            throw new BadRequestHttpException('catalog.product_exist');
        }
        $baseProduct = new CatalogBaseGoods();
        $product = new CatalogGoods();
        $baseProduct->cat_id = $catId;
        if (!empty($post['article'])) {
            $baseProduct->article = $post['article'];
        }
        $baseProduct->product = $post['name'];
        if (!empty($post['category_id'])) {
            $baseProduct->category_id = (int)$post['category_id'];
        }
        $baseProduct->units = !empty($post['units']) && is_float($post['units']) ? $post['units'] : 1;
        $baseProduct->ed = $post['ed'];
        $baseProduct->price = $product->price = $post['price'];
        $baseProduct->status = !empty($post['status']) && in_array($post['status'], [0, 1]) ? $post['status'] : 1;
        $baseProduct->supp_org_id = $post['vendor_id'];
        if (!empty($post['product_image'])) {
            $baseProduct->image = WebApiHelper::convertLogoFile($post['product_image']);
        }
        $product->cat_id = $catId;
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$baseProduct->save()) {
                throw new ValidationException($baseProduct->getFirstErrors());
            }
            $productId = $baseProduct->id;
            $product->base_goods_id = $productId;
            if (!$product->save()) {
                throw new ValidationException($product->getFirstErrors());
            }
            $t->commit();
        } catch (\Throwable $e) {
            $t->rollBack();
            throw $e;
        }
        return $this->addProductResult($post);
    }

    /**
     * @param array $post
     * @return array
     */
    private function addProductResult(array $post)
    {
        $result = [
            'vendor_id'     => '',
            'article'       => '',
            'category_id'   => '',
            'price'         => '',
            'ed'            => '',
            'units'         => 1,
            'status'        => 1,
            'product_image' => false,
        ];
        $result['vendor_id'] = $post['vendor_id'];
        $result['article'] = $post['article'];
        $result['name'] = $post['name'];
        if (!empty($post['category_id'])) {
            $result['category_id'] = $post['category_id'];
        }
        $result['price'] = $post['price'];
        $result['ed'] = $post['ed'];
        if (!empty($post['units'])) {
            $result['units'] = $post['units'];
        }
        if (!empty($post['status'])) {
            $result['status'] = $post['status'];
        }
        if (!empty($post['product_image'])) {
            $result['product_image'] = true;
        }
        return $result;
    }
}
