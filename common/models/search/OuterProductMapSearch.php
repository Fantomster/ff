<?php

namespace common\models\search;

use common\helpers\DBNameHelper;
use common\models\CatalogBaseGoods;
use common\models\OuterProduct;
use common\models\OuterProductMap;
use common\models\OuterStore;
use common\models\OuterUnit;
use yii\data\SqlDataProvider;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OuterProductMapSearch extends OuterProductMap
{
    /**
     * @param $client
     * @param $post
     * @return SqlDataProvider
     */
    public function search($client, $post)
    {
        $dbName = "`" . DBNameHelper::getApiName() . "`.";
        $outerProductMapTableName = $dbName . OuterProductMap::tableName();
        $outerProductTableName = $dbName . OuterProduct::tableName();
        $outerUnitTableName = $dbName . OuterUnit::tableName();
        $outerStoreTableName = $dbName . OuterStore::tableName();
        $catalogBaseGoodsTableName = CatalogBaseGoods::tableName();

        $this->service_id = $post['service_id'] ?? 0;
        $mainOrgId = OuterProductMap::getMainOrg($client->id) ?? $client->id;

        $query = CatalogBaseGoods::find()
            ->select([
                "$outerProductMapTableName.id as id",
                "IFNULL($outerProductMapTableName.service_id, :service_id) as service_id",
                "IFNULL($outerProductMapTableName.organization_id, :client_id) as organization_id",
                "$catalogBaseGoodsTableName.supp_org_id as vendor_id",
                "vendor.name as vendor_name",
                "$catalogBaseGoodsTableName.id as product_id",
                "$catalogBaseGoodsTableName.product as product_name",
                "$catalogBaseGoodsTableName.ed as unit",
                "$outerProductTableName.id as outer_product_id",
                "$outerProductTableName.name as outer_product_name",
                "$outerUnitTableName.id as outer_unit_id",
                "$outerUnitTableName.name as outer_unit_name",
                "$outerStoreTableName.id as outer_store_id",
                "$outerStoreTableName.name as outer_store_name",
                "$outerProductMapTableName.coefficient as coefficient",
                "$outerProductMapTableName.vat as vat",
                "$outerProductMapTableName.created_at as created_at",
                "$outerProductMapTableName.updated_at as updated_at"
            ])
            ->leftJoin("$outerProductMapTableName", "$outerProductMapTableName.product_id = $catalogBaseGoodsTableName.id 
            and $outerProductMapTableName.service_id = :service_id and $outerProductMapTableName.organization_id = IF(product_id in 
            (select product_id from $outerProductMapTableName where service_id = :service_id and organization_id = :client_id), :client_id, :mainOrgId)")
            ->leftJoin("$outerProductTableName", "$outerProductTableName.id = $outerProductMapTableName.outer_product_id")
            ->leftJoin("$outerUnitTableName", "$outerUnitTableName.id = $outerProductMapTableName.outer_unit_id")
            ->leftJoin("$outerStoreTableName", "$outerStoreTableName.id = $outerProductMapTableName.outer_store_id")
            ->leftJoin("organization vendor", "$catalogBaseGoodsTableName.supp_org_id = vendor.id")
            ->where(["$catalogBaseGoodsTableName.deleted" => 0])
            ->params([':service_id' => $this->service_id, ':client_id' => $client->id, ':mainOrgId' => $mainOrgId]);

        if (!$this->service_id) {
            $query->andWhere("service_id = :service_id", [':service_id' => $this->service_id]);
        }

        $vendors = array_keys($client->getSuppliers(null));

        if (isset($post['search'])) {
            /**
             * фильтр по продукту
             */
            if (!empty($post['search']['product'])) {
                $query->andFilterWhere(['like', "$outerProductTableName.`name`", $post['search']['product']]);
                $query->orFilterWhere(['like', "`$catalogBaseGoodsTableName`.`product`", $post['search']['product']]);
            }
            /**
             * фильтр по поставщику
             */
            if (!empty($post['search']['vendor'])) {
                $vendors = [$post['search']['vendor']];
            }
        }

        $query->andWhere(['in', "$catalogBaseGoodsTableName.supp_org_id", $vendors]);
        $query->orderBy([
            'IF(outer_product_id is null, 0, 1)' => SORT_DESC,
            'outer_product_id' => SORT_ASC,
            'product_id' => SORT_ASC,
        ]);

        $dataProvider = new SqlDataProvider([
            'sql' => $query->createCommand()->getRawSql()
        ]);

        return $dataProvider;
    }

}
