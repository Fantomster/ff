<?php

namespace api\common\models\rkws;

use api\common\models\iiko\iikoService;
use yii\data\SqlDataProvider;
use common\models\Catalog;

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
            'product_rid' => 'Продукт в системе Заказчика',
            'store' => 'Склад',
            'koef' => 'Коэффициент',
            'vat' => 'Ставка НДС',
            'pdenom' => 'Название продукта сопоставления',
            'service_id' => 'Сервис',
            'service_denom' => 'Сервис'
        ];
    }


    /**
     * Search
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
            0 => [],
            1 => ["fprod.denom as pdenom", "fstore.name as store", "fprod.unitname as unitname"], // R-keeper
            2 => ["fprod.denom as pdenom", "fstore.denom as store", "fprod.unit as unitname"], // iiko
            8 => ["fprod.name as pdenom", "fstore.name as store", "fprod.measure as unitname"], // 1C
        ];

        $joins = [
            0 => '',
            1 => " LEFT JOIN `$dbName`.`rk_product` fprod ON fmap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`rk_storetree` fstore ON fmap.store_rid = fstore.id AND fmap.org_id = fstore.acc  AND fstore.type = 2 ",

            2 => " LEFT JOIN `$dbName`.`iiko_product` fprod ON fmap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`iiko_store` fstore ON fmap.store_rid = fstore.id AND fmap.org_id = fstore.org_id  AND fstore.is_active = 1 ",

            8 => " LEFT JOIN `$dbName`.`one_s_good` fprod ON fmap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`one_s_store` fstore ON fmap.store_rid = fstore.id AND fmap.org_id = fstore.org_id ",

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

        $fieldsCBG = array_merge([
            'cbg.id', 'cbg.product', 'cbg.supp_org_id', /*'cbg.units', 'cbg.price', */
            'cbg.cat_id', /*'cbg.category_id',*/
            'cbg.article', /*'cbg.note', */
            'cbg.ed', 'org.name',
            /*"(`cbg`.`article` + 0) AS c_article_1",
            "`cbg`.`article` AS c_article", "`cbg`.`article` REGEXP '^-?[0-9]+$' AS i",*/
            /*"`cbg`.`product` REGEXP '^-?[а-яА-Я].*$' AS `alf_cyr`", 'cbg.updated_at',*/
            "fmap.id as fmap_id", "fmap.vat as vat", "fmap.koef as koef", "fmap.service_id as service_id",
            "allservice.denom as service_denom"
        ], $fields[$this->service_id]);

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
        $where_all = '';
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
            $where .= ' AND `org`.id IN (' . $this->selectedVendor . ') ';
        }

        if (!empty($this->searchString)) {
            $where .= 'AND (cbg.product  LIKE :searchString OR cbg.article LIKE :searchString)';
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

        if (!$this->service_id) {
            $where_all .= ' AND service_id = 0';
        }

        $client_id = $this->client->id;
        if ($this->service_id == 2) {
            $mainOrg_id = iikoService::getMainOrg($this->client->id);
            if ($mainOrg_id != $this->client->id) {
                $client_id = "IF(product_id in (select product_id from `$dbName`.all_map where service_id = 2 and org_id = $client_id), $client_id";

                if (!empty($mainOrg_id)) {
                    $client_id .= ", $mainOrg_id";
                }
                $client_id .= ")";
            }

        }

        $sql = "SELECT " . implode(',', $fieldsCBG) . "
        FROM `catalog_base_goods` `cbg`
             LEFT JOIN `organization` `org` ON cbg.supp_org_id = org.id
             LEFT JOIN `catalog` `cat` ON  cbg.cat_id = cat.id AND cat.type = 1
             LEFT JOIN `$dbName`.`all_map` `fmap` ON cbg.id = fmap.product_id AND fmap.org_id = " . $client_id . " AND fmap.service_id = " . $this->service_id . "
             " . $joins[$this->service_id] . "
             LEFT JOIN `$dbName`.`all_service` `allservice` ON fmap.service_id = allservice.id       
           WHERE          
           cbg.deleted = 0  AND cbg.cat_id IN (" . $this->catalogs . ")
           " . $where . "
           AND cbg.deleted = 0".$where_all;

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
            'sql' => $query->sql,
            'params' => $params_sql,

            'pagination' => [
                'page' => isset($params['page']) ? ($params['page'] - 1) : 0,
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : null,
                'params' => [
                    'sort' => isset($params['sort']) ? $params['sort'] : 'product',
                ]
            ],
            'key' => 'id',
            'sort' => [
                'attributes' => [
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
