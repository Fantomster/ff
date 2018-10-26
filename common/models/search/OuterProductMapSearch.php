<?php

namespace common\models\search;

use common\helpers\DBNameHelper;
use common\models\CatalogBaseGoods;
use common\models\OuterProduct;
use common\models\OuterProductMap;
use common\models\OuterStore;
use common\models\OuterUnit;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OuterProductMapSearch extends OuterProductMap
{
    /**
     * Search
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($client, $post)
    {
        $dbName = "`".DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn)."`";
        $outerProductMapTableName = OuterProductMap::tableName();
        $outerProductTableName = OuterProduct::tableName();
        $catalogBaseGoodsTableName = CatalogBaseGoods::tableName();
        $outerUnitTableName = OuterUnit::tableName();
        $outerStoreTableName = OuterStore::tableName();

        $this->service_id = $post['service_id'] ?? 0;
        $mainOrgId = OuterProductMap::getMainOrg($client->id) ?? $client->id;

        $query = CatalogBaseGoods::find()
            ->select(["$dbName.$outerProductMapTableName.id as id", "IFNULL($dbName.$outerProductMapTableName.id, :service_id) as service_id",
                "IFNULL($dbName.$outerProductMapTableName.organization_id, :client_id) as organization_id, $catalogBaseGoodsTableName.supp_org_id as vendor_id",
                "$catalogBaseGoodsTableName.id as product_id", "$catalogBaseGoodsTableName.product as product_name", "$catalogBaseGoodsTableName.ed as unit",
                "$dbName.$outerProductTableName.id as outer_product_id", "$dbName.$outerProductTableName.name as outer_product_name",
                "$dbName.$outerUnitTableName.id as outer_unit_id", "$dbName.$outerUnitTableName.name as outer_unit_name",
                "$dbName.$outerStoreTableName.id as outer_store_id", "$dbName.$outerStoreTableName.name as outer_store_name",
                "$dbName.$outerProductMapTableName.coefficient as coefficient", "$dbName.$outerProductMapTableName.vat as vat",
                "$dbName.$outerProductMapTableName.created_at as created_at", "$dbName.$outerProductMapTableName.updated_at as updated_at"])
            ->leftJoin("$dbName.$outerProductMapTableName", "$dbName.$outerProductMapTableName.product_id = $catalogBaseGoodsTableName.id 
            and $dbName.$outerProductMapTableName.service_id = :service_id and $dbName.$outerProductMapTableName.organization_id = IF(product_id in 
            (select product_id from $dbName.$outerProductMapTableName where service_id = :service_id and organization_id = :client_id), :client_id, :mainOrgId)")
            ->leftJoin("$dbName.$outerProductTableName", "$dbName.$outerProductTableName.id = $dbName.$outerProductMapTableName.outer_product_id")
            ->leftJoin("$dbName.$outerUnitTableName", "$dbName.$outerUnitTableName.id = $dbName.$outerProductMapTableName.outer_unit_id")
            ->leftJoin("$dbName.$outerStoreTableName", "$dbName.$outerStoreTableName.id = $dbName.$outerProductMapTableName.outer_store_id")
            ->where(["$catalogBaseGoodsTableName.deleted" => 0])
            ->params([':service_id' => $this->service_id, ':client_id' => $client->id, ':mainOrgId' => $mainOrgId]);

        if (!$this->service_id) {
            $query->andWhere("service_id = :service_id", [':service_id' => $this->service_id]);
        }

        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $vendors = array_keys($client->getSuppliers(null));

        if (isset($post['search'])) {
            /**
             * фильтр по продукту
             */
            if (!empty($post['search']['product'])) {
                $query->andFilterWhere(['like', "$dbName.`$outerProductTableName`.`name`", $post['search']['product']]);
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

        $dataProvider = new SqlDataProvider([
            'sql'        => $query->createCommand()->getRawSql(),
            'pagination' => [
                'page'     => $page - 1,
                'pageSize' => $pageSize
            ],
            'key'        => 'product_id',
        ]);

        return $dataProvider;
    }

}
