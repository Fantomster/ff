<?php
/**
 * Date: 13.02.2019
 * Author: Mike N.
 * Time: 15:19
 */

namespace api_web\classes;

use api_web\components\definitions\Organization;
use api_web\helpers\CurrencyHelper;
use api_web\helpers\WebApiHelper;
use common\models\
{
    Catalog,
    CatalogGoods,
    RelationSuppRest
};
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
            'rest_org_id' => $this->user->organization_id
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
        $org = \common\models\Organization::find()
            ->where([
                'id'      => $post['vendor_id'],
                'type_id' => \common\models\Organization::TYPE_LAZY_VENDOR
            ])->one();
        if (!empty($org)) {
            throw new BadRequestHttpException('catalog.not_lazy_vendor');
        }
        $catId = $this->user->organization->getCatalogs($post['vendor_id']);
        if ($catId < 0) {
            throw new BadRequestHttpException('catalog.no_such_vendor');
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
}
