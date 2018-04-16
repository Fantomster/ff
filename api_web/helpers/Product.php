<?php

namespace api_web\helpers;


use api_web\components\WebApi;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\RelationSuppRest;

class Product extends WebApi
{
    /**
     * @param $id
     * @param array $catalogs
     * @return array
     */
    public function findFromCatalogs($id, $catalogs = [])
    {
        if(empty($catalogs)) {
            $catalogs = explode(',', $this->user->organization->getCatalogs());
        }

        $model = CatalogBaseGoods::findOne(['id' => $id]);

        $individualModel = CatalogGoods::find()->where(['base_goods_id' => $id])
            ->andWhere(['IN', 'cat_id', $catalogs])
            ->one();

        return $this->prepareProduct($model, $individualModel);
    }

    /**
     * @param $id
     * @param $vendor_id
     * @param $client_id
     * @return array
     */
    public function findFromVendor($id, $vendor_id, $client_id)
    {
        if (empty($client_id)) {
            $client_id = $this->user->organization->id;
        }

        $relation = RelationSuppRest::findOne(['rest_org_id' => $client_id, 'supp_org_id' => $vendor_id]);

        if (empty($relation) || $relation->cat_id == 0) {
            return [];
        }

        $model = CatalogBaseGoods::findOne(['id' => $id]);
        $individualModel = CatalogGoods::find()->where(['cat_id' => $relation->cat_id, 'base_goods_id' => $id])->one();
        return $this->prepareProduct($model, $individualModel);
    }

    /**
     * @param CatalogBaseGoods $baseModel
     * @param CatalogGoods $individualModel
     * @return array
     */
    private function prepareProduct(CatalogBaseGoods $baseModel, CatalogGoods $individualModel = null)
    {
        $product = $baseModel->getAttributes();

        if (!empty($individualModel)) {
            $product['price'] = $individualModel->price;
            $product['discount'] = $individualModel->discount;
            $product['discount_percent'] = $individualModel->discount_percent;
            $product['discount_fixed'] = $individualModel->discount_fixed;
            $product['cat_id'] = $individualModel->cat_id;
        }

        $product['vendor_id'] = Catalog::findOne($product['cat_id'])->supp_org_id;
        $product['model'] = $baseModel;

        return $product;
    }

}