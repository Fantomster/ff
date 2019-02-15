<?php

namespace console\controllers;

use api\common\models\AllMaps;
use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoStore;
use api\common\models\RkProduct;
use api\common\models\RkStoretree;
use common\models\OuterProduct;
use common\models\OuterProductMap;
use common\models\OuterStore;
use yii\console\Controller;

class TransferIntegrationController extends Controller
{
    public function actionAllmap($orgList)
    {
        echo "START Transfer AllMap organizations:" . $orgList . PHP_EOL;
        echo "Transfer iiko" . PHP_EOL;

        $outerProductMap = OuterProductMap::tableName();
        $outerProduct = OuterProduct::tableName();
        $outerStore = OuterStore::tableName();

        $allMap = AllMaps::tableName();

        $iikoProduct = iikoProduct::tableName();
        $iikoStore = iikoStore::tableName();

        $selQuery = "SELECT DISTINCT
  iiko.*
FROM (SELECT
        (
          SELECT id
          FROM $outerProduct
          WHERE
              outer_uid = (
              SELECT uuid
              FROM $iikoProduct
              WHERE id = serviceproduct_id
            )
            AND
            $outerProduct.service_id = all_map.service_id
            AND 
            $outerProduct.org_id = all_map.org_id
        )                                         as outer_product_id,
        (
          SELECT id
          FROM $outerStore
          WHERE
              outer_uid = (
              SELECT uuid
              FROM $iikoStore
              WHERE id = all_map.store_rid
            )
            AND
            $outerStore.service_id = all_map.service_id
               AND 
            $outerStore.org_id = all_map.org_id
        )                                         as outer_store_id,
        NULL                                      as outer_unit_id,
        org_id                                    as organization_id,
        product_id                                as product_id,
        service_id                                as service_id,
        koef                                      as coefficient,
        IF(vat != '0', REPLACE(vat, '00', ''), 0) as vat,
        supp_id                                   as vendor_id,
        NOW()                                     as created_at,
        NOW()                                     as updated_at
      from $allMap
      where
        org_id in ($orgList)
        and service_id = 2
     ) as iiko
LEFT JOIN $outerProductMap opm ON
        iiko.product_id = opm.product_id AND
        iiko.vendor_id = opm.vendor_id AND
        iiko.organization_id = opm.organization_id
WHERE
  iiko.outer_product_id is not null
  AND
  opm.id is null;";

        if (count(\Yii::$app->db_api->createCommand($selQuery)->queryAll()) > 0) {
            $query = "INSERT INTO $outerProductMap (outer_product_id, outer_store_id, outer_unit_id, organization_id, product_id, service_id, coefficient, vat, vendor_id, created_at, updated_at) $selQuery";

            \Yii::$app->db_api->createCommand($query)->execute();
        }

        echo "Transfer r-keeper" . PHP_EOL;

        $rkProduct = RkProduct::tableName();
        $rkStore = RkStoretree::tableName();

        $selQuery = "
SELECT DISTINCT
  rkws.*
FROM (SELECT
        (
          SELECT id
          FROM $outerProduct
          WHERE
              outer_uid = (
              SELECT rid
              FROM $rkProduct
              WHERE id = serviceproduct_id
            )
            AND
            $outerProduct.service_id = all_map.service_id
            AND 
            $outerProduct.org_id = all_map.org_id
        )                                         as outer_product_id,
        (
          SELECT id
          FROM $outerStore
          WHERE
              outer_uid = (
              SELECT rid
              FROM $rkStore
              WHERE id = all_map.store_rid and active = 1
            )
            AND
            $outerStore.service_id = all_map.service_id
            AND
            $outerStore.org_id = all_map.org_id
        )                                         as outer_store_id,
        NULL                                      as outer_unit_id,
        org_id                                    as organization_id,
        product_id                                as product_id,
        service_id                                as service_id,
        koef                                      as coefficient,
        IF(vat != '0', REPLACE(vat, '00', ''), 0) as vat,
        supp_id                                   as vendor_id,
        NOW()                                     as created_at,
        NOW()                                     as updated_at
      from $allMap
      where
        org_id in ($orgList)
        and service_id = 1
     ) as rkws
LEFT JOIN $outerProductMap opm ON
        rkws.product_id = opm.product_id AND
        rkws.vendor_id = opm.vendor_id AND
        rkws.organization_id = opm.organization_id
WHERE
  rkws.outer_product_id is not null
  AND
  opm.id is null;";

        if (count(\Yii::$app->db_api->createCommand($selQuery)->queryAll()) > 0) {
            $query = "INSERT INTO $outerProductMap (outer_product_id, outer_store_id, outer_unit_id, organization_id, product_id, service_id, coefficient, vat, vendor_id, created_at, updated_at) $selQuery";

            \Yii::$app->db_api->createCommand($query)->execute();
        }
        echo "FINISH" . PHP_EOL;
    }

}
