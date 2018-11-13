<?php

namespace api\common\models\rkws;

use api\common\models\iiko\iikoService;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\RelationSuppRest;
use yii\data\SqlDataProvider;
use common\models\Catalog;
use api_web\components\Registry;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderCatalogSearchMap extends \common\models\search\OrderCatalogSearch
{
    public $product_rid;
    public $vat;
    public $store;
    public $koef;
    public $pdenom;
    public $service_id;
    public $vendors;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['product_rid', 'vat', 'store', 'koef', 'pdenom', 'service_id'], 'safe'],
            //   [['page','count'], 'integer']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'product_rid'   => 'Продукт в системе Заказчика',
            'store'         => 'Склад',
            'koef'          => 'Коэффициент',
            'vat'           => 'Ставка НДС',
            'pdenom'        => 'Название продукта сопоставления',
            'service_id'    => 'Сервис',
            'service_denom' => 'Сервис'
        ];
    }

    /**
     * Search
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->load($params);

        $db_api = \Yii::$app->db_api;
        $dbName = $this->getDsnAttribute('dbname', $db_api->dsn);
        if (empty($this->service_id)) {
            $this->service_id = 0;
        }
        $fields = [
            0                                 => '',
            Registry::RK_SERVICE_ID           => ',fprod.denom as pdenom, fstore.name as store, fprod.unitname as unitname', // R-keeper
            Registry::IIKO_SERVICE_ID         => ',fprod.denom as pdenom, fstore.denom as store, fprod.unit as unitname', // iiko
            Registry::ONE_S_CLIENT_SERVICE_ID => ',fprod.name as pdenom, fstore.name as store, fprod.measure as unitname', // 1C
            Registry::TILLYPAD_SERVICE_ID     => ',fprod.denom as pdenom, fstore.denom as store, fprod.unit as unitname', // tillypad
        ];
        //print_r($fields);die();

        $joins = [
            0                       => '',
            Registry::RK_SERVICE_ID => " LEFT JOIN `$dbName`.`rk_product` fprod ON amap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`rk_storetree` fstore ON amap.store_rid = fstore.id AND amap.org_id = fstore.acc  AND fstore.type = 2 ",

            Registry::IIKO_SERVICE_ID => " LEFT JOIN `$dbName`.`iiko_product` fprod ON amap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`iiko_store` fstore ON amap.store_rid = fstore.id AND amap.org_id = fstore.org_id  AND fstore.is_active = 1 ",

            Registry::ONE_S_CLIENT_SERVICE_ID => " LEFT JOIN `$dbName`.`one_s_good` fprod ON amap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`one_s_store` fstore ON amap.store_rid = fstore.id AND amap.org_id = fstore.org_id ",

            Registry::TILLYPAD_SERVICE_ID => " LEFT JOIN `$dbName`.`iiko_product` fprod ON amap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`iiko_store` fstore ON amap.store_rid = fstore.id AND amap.org_id = fstore.org_id  AND fstore.is_active = 1 ",

        ];

        /*$fieldsCBG = array_merge([
            'cbg.id', 'cbg.product', 'cbg.supp_org_id', 'cbg.units', 'cbg.price', 'cbg.cat_id', 'cbg.category_id',
            'cbg.article', 'cbg.note', 'cbg.ed', 'curr.symbol', 'org.name',
            "(`cbg`.`article` + 0) AS c_article_1",
            "`cbg`.`article` AS c_article", "`cbg`.`article` REGEXP '^-?[0-9]+$' AS i",
            "`cbg`.`product` REGEXP '^-?[а-яА-Я].*$' AS `alf_cyr`", 'cbg.updated_at',
            "curr.id as currency_id", "fmap.id as fmap_id", "fmap.vat as vat", "fmap.koef as koef", "fmap.service_id as service_id",
            "allservice.denom as service_denom"
        ], $fields[$this->service_id]);*/

        //$fieldsCBG = array_merge([
            //'cbg.id', 'cbg.product', 'cbg.supp_org_id', /*'cbg.units', 'cbg.price', */
            //'cbg.cat_id', /*'cbg.category_id',*/
            //'cbg.article', /*'cbg.note', */
            //'cbg.ed', 'org.name',
            /*"(`cbg`.`article` + 0) AS c_article_1",
            "`cbg`.`article` AS c_article", "`cbg`.`article` REGEXP '^-?[0-9]+$' AS i",*/
            /*"`cbg`.`product` REGEXP '^-?[а-яА-Я].*$' AS `alf_cyr`", 'cbg.updated_at',*/
            //"fmap.id as fmap_id", "fmap.vat as vat", "fmap.koef as koef", "fmap.service_id as service_id",
            //"allservice.denom as service_denom"
        //], $fields[$this->service_id]);

        /*$fieldsCG = array_merge([
            'cbg.id', 'cbg.product', 'cbg.supp_org_id', 'cbg.units', 'cg.price', 'cg.cat_id', 'cbg.category_id',
            'cbg.article', 'cbg.note', 'cbg.ed', 'curr.symbol', 'org.name',
            "(`cbg`.`article` + 0) AS c_article_1",
            "`cbg`.`article` AS c_article", "`cbg`.`article` REGEXP '^-?[0-9]+$' AS i",
            "`cbg`.`product` REGEXP '^-?[а-яА-Я].*$' AS `alf_cyr`", 'coalesce( cg.updated_at, cbg.updated_at) AS updated_at',
            "curr.id as currency_id", "fmap.id as fmap_id", "fmap.vat as vat", "fmap.koef as koef", "fmap.service_id as service_id",
            "allservice.denom as service_denom"
        ], $fields[$this->service_id]);*/

        $where = '';
        //$where_all = '';
        $params_sql = [];

        if (!empty($this->selectedVendor)) {
            if (is_array($this->selectedVendor)) {
                foreach ($this->selectedVendor as $key => $supp_org_id) {
                    $this->selectedVendor[$key] = (int)$supp_org_id;
                }
                $this->selectedVendor = implode(', ', $this->selectedVendor);
            } else {
                $this->selectedVendor = (int)$this->selectedVendor;
            }
            //$where .= ' AND `org`.id IN (' . $this->selectedVendor . ') ';
        }

        if (!empty($this->searchString)) {
            $where .= ' AND (acp.product  LIKE :searchString OR acp.article LIKE :searchString)';
            $params_sql[':searchString'] = "%" . $this->searchString . "%";
        }

        /*if (!empty($this->searchCategory)) {
            if (is_array($this->searchCategory)) {
                foreach ($this->searchCategory as $key => $category_id) {
                    $this->searchCategory[$key] = (int)$category_id;
                }
                $this->searchCategory = implode(', ', $this->searchCategory);
            } else {
                $this->searchCategory = (int)$this->searchCategory;
            }
            $where .= ' AND category_id IN (' . $this->searchCategory . ') ';
        }

        if ($this->searchCategory === 0) {
            $where .= ' AND category_id IS NULL ';
        }

        if (!empty($this->searchPrice)) {
            if (isset($this->searchPrice['from'])) {
                $params_sql[':price_start'] = $this->searchPrice['from'];
                $where_all .= ' AND price >= :price_start ';
            }
            if (isset($this->searchPrice['to'])) {
                $params_sql[':price_end'] = $this->searchPrice['to'];
                $where_all .= ' AND price <= :price_end ';
            }
        }*/

        /*if (!$this->service_id) {
            $where_all .= ' AND service_id = 0';
        }*/

        $client_id = $this->client->id;
        $vendorInList = $this->selectedVendor;
        //$fields_sql = ''.$fields[$this->service_id];

        if (isset($this->vendors) && empty($this->selectedVendor)) {
            $arrayVendorsId = array_keys($this->vendors);
            unset($arrayVendorsId[0]);
            $arrayVendorsId = implode(",", $arrayVendorsId);
            $vendorInList = $arrayVendorsId;
            //$where_all .= " AND cbg.supp_org_id in ($vendorInList)";
        }/* else {
            $where_all .= " AND cbg.supp_org_id in (" . $vendorInList . ")";
        }*/

        /*$sql = "SELECT " . implode(',', $fieldsCBG) . "
        FROM `catalog_base_goods` `cbg`
             LEFT JOIN `organization` `org` ON cbg.supp_org_id = org.id
             LEFT JOIN `$dbName`.`all_map` `fmap` ON cbg.id = fmap.product_id AND fmap.org_id = " . $client_id . " AND fmap.service_id = " . $this->service_id . "
             " . $joins[$this->service_id] . "
             LEFT JOIN `$dbName`.`all_service` `allservice` ON fmap.service_id = allservice.id
             JOIN (
                SELECT sqc.base_goods_id, sqa.supp_org_id
                  FROM relation_supp_rest sqa
                  LEFT JOIN catalog_goods sqc ON sqc.cat_id = sqa.cat_id
                 WHERE sqa.supp_org_id in (" . $vendorInList . ")
                   AND sqa.rest_org_id = " . $client_id . "
                   GROUP BY sqc.base_goods_id, sqa.supp_org_id
             ) catg ON catg.supp_org_id = cbg.supp_org_id AND cbg.id = case WHEN catg.base_goods_id IS NULL THEN cbg.id ELSE catg.base_goods_id END
           WHERE
           cbg.deleted = 0
           " . $where . $where_all;*/

        $sql = "SELECT DISTINCT acp.catalog_id as cat_id,acp.product_id as id,acp.product,acp.article,acp.ed,amap.id as amap_id,amap.vat as vat,amap.koef as koef,amap.service_id as service_id,aser.denom as service_denom" . $fields[$this->service_id] .
            " FROM `assigned_catalog_products` `acp`
            LEFT JOIN `$dbName`.`all_map` `amap` ON acp.product_id = amap.product_id AND amap.org_id = " . $client_id . " AND amap.service_id = " . $this->service_id . " 
            LEFT JOIN `$dbName`.`all_service` `aser` ON amap.service_id = aser.id " . $joins[$this->service_id] . "
            WHERE acp.rest_org_id = " . $client_id . " 
              AND acp.supp_org_id in (" . $vendorInList . ") 
              AND acp.catalog_status = 1 
              AND acp.deleted = 0" . $where;
        /*$sql = "
        SELECT DISTINCT * FROM (
           SELECT 
              " . implode(',', $fieldsCBG) . "
           FROM `catalog_base_goods` `cbg`
             LEFT JOIN `organization` `org` ON cbg.supp_org_id = org.id
             LEFT JOIN `catalog` `cat` ON  cbg.cat_id = cat.id AND cat.type = 1
             LEFT JOIN `currency` `curr` ON cat.currency_id = curr.id
             LEFT JOIN `$dbName`.`all_map` fmap ON cbg.id = fmap.product_id AND fmap.org_id = " . $client_id . " AND fmap.service_id = " . $this->service_id . "
             " . $joins[$this->service_id] . "
             LEFT JOIN `$dbName`.`all_service` allservice ON fmap.service_id = allservice.id       
           WHERE          
           cbg.cat_id IN (" . $this->catalogs . ")
           " . $where . "
           AND cbg.deleted = 0 
        UNION ALL
          SELECT 
          " . implode(',', $fieldsCG) . "
          FROM `catalog_goods` `cg`
           LEFT JOIN `catalog_base_goods` `cbg` ON cg.base_goods_id = cbg.id
           LEFT JOIN `organization` `org` ON cbg.supp_org_id = org.id
           LEFT JOIN `catalog` `cat` ON cg.cat_id = cat.id
           LEFT JOIN `currency` `curr` ON cat.currency_id = curr.id
           LEFT JOIN `$dbName`.`all_map` fmap ON cbg.id = fmap.product_id AND fmap.org_id = " . $client_id . " AND fmap.service_id = " . $this->service_id . "
           " . $joins[$this->service_id] . "
           LEFT JOIN `$dbName`.`all_service` allservice ON fmap.service_id = allservice.id 
          WHERE         
          cg.cat_id IN (" . $this->catalogs . ")
          " . $where . "
          AND cbg.deleted = 0     
        ) as c WHERE id != 0 " . $where_all;*/

        $query = \Yii::$app->db->createCommand($sql);

        $dataProvider = new SqlDataProvider([
            'sql'    => $query->sql,
            'params' => $params_sql,

            'pagination' => [
                'page'     => isset($params['page']) ? ($params['page'] - 1) : 0,
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : null,
                'params'   => [
                    'sort' => isset($params['sort']) ? $params['sort'] : 'product',
                ]
            ],
            'key'        => 'id',
            'sort'       => [
                'attributes'   => [
                    'product',
                    /* => [
                        'asc' => ['product' => SORT_ASC],
                        'desc' => ['product' => SORT_DESC],
                      //  'default' => SORT_ASC
                    ],*/
                    /*      'price',
                          'units',
                          'article',
                          'name',
                          'c_article_1',
                          'c_article',
                          'i',
                          'pdenom',
                          'vat',
                          'store',
                          'unitname',
                          'koef'
      */
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC,
                    // 'c_article_1' => SORT_ASC,
                    // 'c_article' => SORT_ASC
                ]
            ],
        ]);
        return $dataProvider;
    }

    private function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }

}
