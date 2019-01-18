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
        $dbName = DBNameHelper::getApiName() . ".";
        $outerProductMapTableName = $dbName . OuterProductMap::tableName();
        $outerProductTableName = $dbName . OuterProduct::tableName();
        $outerUnitTableName = $dbName . OuterUnit::tableName();
        $outerStoreTableName = $dbName . OuterStore::tableName();

        $this->service_id = $post['service_id'] ?? 0;
        $mainOrgSetting = IntegrationSettingValue::getSettingsByServiceId($this->service_id, $client->id, ['main_org']);
        $mainOrgId = !empty($mainOrgSetting) ? $mainOrgSetting : $client->id;

        $query = (new Query())->select([
            "coalesce(e_c.id, e_m.id) id",
            "e_c.outer_store_id",
            "e_m.service_id",
            "e_c.organization_id",
            "d_v.id vendor_id",
            "d_v.name vendor_name",
            "d.id product_id",
            "d.product product_name",
            "d.ed unit",
            "f.id outer_product_id",
            "f.name outer_product_name",
            "g.id outer_unit_id",
            "g.name outer_unit_name",
            "h.name outer_store_name",
            "e_m.coefficient",
            "e_c.vat",
            "coalesce(e_c.created_at, e_m.created_at) created_at",
            "coalesce(e_c.updated_at, e_m.updated_at)"
        ])
            ->from("relation_supp_rest a")
            ->innerJoin('catalog b', 'b.id = a.cat_id and b.type = 2')
            ->innerJoin('catalog_goods c', 'c.cat_id = a.cat_id')
            ->innerJoin('catalog_base_goods d', 'd.id = c.base_goods_id')
            ->innerJoin('organization d_v', 'd_v.id = d.supp_org_id')
            ->leftJoin("$outerProductMapTableName e_m", "e_m.product_id=d.id and e_m.service_id=:service_id and e_m.organization_id =:parent_org")
            ->leftJoin("$outerProductMapTableName e_c", 'e_c.product_id = d.id and e_c.service_id=:service_id and e_c.organization_id=:real_org_id')
            ->leftJoin("$outerProductTableName f", "f.id=e_m.outer_product_id")
            ->leftJoin("$outerUnitTableName g", "g.id=f.outer_unit_id")
            ->leftJoin("$outerStoreTableName h", "h.id=e_c.outer_store_id")
            ->where(["a.rest_org_id" => $client->id])
            ->params([':service_id' => $this->service_id, ':real_org_id' => $client->id, ':parent_org' => $mainOrgId]);

        $vendors = array_keys($client->getSuppliers(null));

        /**
         * ВНИМАНИЕ !!!!!!!
         *  Сортировка по умолчанию тут выключена специально, поскольку занимает очень много времени > 20 сек
         *  сортировка производится только  если задан поиск по какому то продукту.
         *  ЕСЛИ ТЫ ЗАБРАЛСЯ СЮДА ЧТОБЫ ДОБАВИТЬ СОРТИРОВКУ ПО ЧЬЕЙ ТО ЗАДАЧЕ
         *  ОБСУДИ ЭТО С ТИМЛИДОМ ИЛИ ДИРЕКТОРОМ
         */

        if (isset($post['search'])) {
            /**
             * фильтр по id продукта
             */
            if (!empty($post['search']['product_id'])) {
                $query->andFilterWhere(["=", "d.id", $post['search']['product_id']]);
            } else {
                /**
                 * фильтр по продукту
                 */
                if (!empty($post['search']['product'])) {
                    $query->andFilterWhere(['like', "f.name", $post['search']['product']]);
                    $query->orFilterWhere(['like', "d.product", $post['search']['product']]);
                    $query->orderBy([
                        'IF(f.id is null, 0, 1)' => SORT_DESC
                    ]);
                }
                /**
                 * фильтр по поставщику
                 */
                if (!empty($post['search']['vendor'])) {
                    $vendors = [$post['search']['vendor']];
                }
            }
        }

        $query->andWhere(['in', "a.supp_org_id", $vendors]);

        $dataProvider = new SqlDataProvider([
            'sql' => $query->createCommand()->getRawSql(),
            'key' => 'product_id'
        ]);

        return $dataProvider;
    }

}
