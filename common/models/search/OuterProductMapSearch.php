<?php

namespace common\models\search;

use common\helpers\DBNameHelper;
use common\models\CatalogBaseGoods;
use common\models\IntegrationSettingValue;
use common\models\Organization;
use common\models\OuterProduct;
use common\models\OuterProductMap;
use common\models\OuterStore;
use common\models\OuterUnit;
use yii\data\SqlDataProvider;
use yii\db\Query;

/**
 * @author Sergey Frantsev
 */
class OuterProductMapSearch extends OuterProductMap
{
    /**
     * @param $client
     * @param $post
     * @return SqlDataProvider
     */
    public function search(Organization $client, $post)
    {
        $dbName = "`" . DBNameHelper::getApiName() . "`.";
        $outerProductMapTableName = $dbName . OuterProductMap::tableName();
        $outerProductTableName = $dbName . OuterProduct::tableName();
        $outerUnitTableName = $dbName . OuterUnit::tableName();
        $outerStoreTableName = $dbName . OuterStore::tableName();
        $catalogBaseGoodsTableName = CatalogBaseGoods::tableName();

        $this->service_id = $post['service_id'] ?? 0;
        $mainOrgSetting = IntegrationSettingValue::getSettingsByServiceId($this->service_id, $client->id, ['main_org']);
        $mainOrgId = !empty($mainOrgSetting) ? $mainOrgSetting : $client->id;

        $query = (new Query())->select([
            "IF(b.id=a.id, b.id, a.id) id",
            "$outerStoreTableName.id outer_store_id",
            "IFNULL(a.service_id, :service_id)            as service_id",
            "IFNULL(a.organization_id, :real_org_id)    as organization_id",
            "cbg.supp_org_id vendor_id",
            "vendor.name vendor_name",
            "cbg.id product_id",
            "cbg.product product_name",
            "cbg.ed unit",
            "a.outer_product_id outer_product_id",
            "$outerProductTableName.name   outer_product_name",
            "$outerUnitTableName.id outer_unit_id",
            "$outerUnitTableName.name         outer_unit_name",
            "$outerStoreTableName.name     outer_store_name",
            "a.coefficient coefficient",
            "b.vat vat",
            "a.created_at created_at",
            "a.updated_at updated_at"
        ])
            ->from("catalog_base_goods cbg")
            ->leftJoin("$outerProductMapTableName a", "a.product_id=cbg.id and a.service_id=:service_id and 
            a.organization_id=:parent_org");
        $strChildJoin = 'b.product_id = a.product_id and b.service_id=:service_id and b.organization_id=:real_org_id';
        if ($client->id == $mainOrgId) {
            $strChildJoin .= ' and b.id=a.id';
        }
        $query->leftJoin("$outerProductMapTableName b", $strChildJoin)
            ->leftJoin("$outerProductTableName", "$outerProductTableName.id = a.outer_product_id")
            ->leftJoin("$outerUnitTableName", "$outerUnitTableName.id = a.outer_unit_id")
            ->leftJoin("$outerStoreTableName", "$outerStoreTableName.id = b.outer_store_id")
            ->leftJoin("organization vendor", "cbg.supp_org_id = vendor.id")
            ->leftJoin("relation_supp_rest rsr", "cbg.supp_org_id = rsr.supp_org_id and rsr.rest_org_id=:real_org_id and rsr.status=1")
            ->leftJoin("catalog_goods cg", "cg.cat_id = rsr.cat_id and cbg.id = cg.base_goods_id")
            ->where(["cbg.deleted" => 0])
            ->andWhere('cg.base_goods_id is not NULL')
            ->params([':service_id' => $this->service_id, ':real_org_id' => $client->id, ':parent_org' => $mainOrgId]);

        if (!$this->service_id) {
            $query->andWhere("service_id = :service_id", [':service_id' => $this->service_id]);
        }

        $vendors = array_keys($client->getSuppliers(null));

        if (isset($post['search'])) {
            /**
             * фильтр по id продукта
             */
            if (!empty($post['search']['product_id'])) {
                $query->andFilterWhere(["=", "cbg.`id`", $post['search']['product_id']]);
            } else {
                /**
                 * фильтр по продукту
                 */
                if (!empty($post['search']['product'])) {
                    $query->andFilterWhere(['like', "$outerProductTableName.`name`", $post['search']['product']]);
                    $query->orFilterWhere(['like', "cbg.`product`", $post['search']['product']]);
                }
                /**
                 * фильтр по поставщику
                 */
                if (!empty($post['search']['vendor'])) {
                    $vendors = [$post['search']['vendor']];
                }
            }
        }

        $query->andWhere(['in', "rsr.supp_org_id", $vendors]);

        $query->orderBy([
            'IF(a.outer_product_id is null, 0, 1)' => SORT_DESC,
            'a.outer_product_id'                   => SORT_ASC,
            'product_id'                           => SORT_ASC,
        ]);

        $dataProvider = new SqlDataProvider([
            'sql' => $query->createCommand()->getRawSql(),
            'key' => 'product_id'
        ]);

        return $dataProvider;
    }

}
