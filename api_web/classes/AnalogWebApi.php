<?php
/**
 * Date: 14.02.2019
 * Author: Mike N.
 * Time: 12:16
 */

namespace api_web\classes;

use api_web\components\WebApi;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\Organization;
use common\models\ProductAnalog;
use yii\db\Expression;
use yii\db\Query;

class AnalogWebApi extends WebApi
{
    /**
     * Список аналогов
     *
     * @param $request
     * @return array
     */
    public function getList($request)
    {
        return [$request];
    }

    /**
     * Вренуть список аналогов для продукта
     *
     * @param $request
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function getProductAnalogList($request)
    {
        $this->validateRequest($request, ['product_id']);

        $groupId = (new Query())
            ->select(["gid" => new Expression('COALESCE(parent_id, id)')])
            ->from(ProductAnalog::tableName())
            ->where([
                'client_id'  => $this->user->organization_id,
                'product_id' => trim($request['product_id'])
            ]);

        $query = new Query();
        $result = $query
            ->select([
                'product_id'   => 'cbg.id',
                'product_name' => 'cbg.product',
                'article'      => 'cbg.article',
                'vendor_id'    => 'o.id',
                'vendor_name'  => 'o.name',
                'price'        => 'cg.price',
            ])
            ->from(ProductAnalog::tableName() . ' as pa')
            ->innerJoin(CatalogBaseGoods::tableName() . ' as cbg', "pa.product_id = cbg.id")
            ->innerJoin(CatalogGoods::tableName() . ' as cg', "cbg.id = cg.base_goods_id")
            ->innerJoin(Organization::tableName() . ' as o', "cbg.supp_org_id = o.id")
            ->andWhere(['COALESCE(pa.parent_id, pa.id)' => $groupId])
            ->orderBy(['sort_value' => SORT_ASC])
            ->all();

        return ["items" => $result];
    }
}
