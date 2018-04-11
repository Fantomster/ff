<?php

namespace common\models\search;

use common\models\CatalogBaseGoods;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Query;

/**
 *  Model for order catalog search form
 */
class OrderCatalogSearch extends \yii\base\Model {

    public $searchString;
    public $selectedCategory;
    public $selectedVendor;
    public $searchPrice;
    public $catalogs;
    public $client;
    public $searchCategory;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['product', 'price', 'searchString', 'selectedCategory', 'selectedVendor'], 'safe'],
        ];
    }

    /**
     * Search
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params) {
        $this->load($params);

        $fieldsCBG = [
            'cbg.id', 'cbg.product', 'cbg.supp_org_id', 'cbg.units', 'cbg.price', 'cbg.cat_id', 'cbg.category_id',
            'cbg.article', 'cbg.note', 'cbg.ed', 'curr.symbol', 'org.name',
            "(`cbg`.`article` + 0) AS c_article_1",
            "`cbg`.`article` AS c_article", "`cbg`.`article` REGEXP '^-?[0-9]+$' AS i",
            "`cbg`.`product` REGEXP '^-?[а-яА-Я].*$' AS `alf_cyr`"
        ];
        $fieldsCG = [
            'cbg.id', 'cbg.product', 'cbg.supp_org_id', 'cbg.units', 'cg.price', 'cg.cat_id', 'cbg.category_id',
            'cbg.article', 'cbg.note', 'cbg.ed', 'curr.symbol', 'org.name',
            "(`cbg`.`article` + 0) AS c_article_1",
            "`cbg`.`article` AS c_article", "`cbg`.`article` REGEXP '^-?[0-9]+$' AS i",
            "`cbg`.`product` REGEXP '^-?[а-яА-Я].*$' AS `alf_cyr`"
        ];

        $where = '';
        $where_all = '';
        $params_sql = [];
        if(!empty($this->searchString)) {
            $where .= 'AND (cbg.product LIKE :searchString OR cbg.article LIKE :searchString)';
            $params_sql[':searchString'] = "%" . $this->searchString . "%";
        }

        if(!empty($this->selectedVendor)) {
            if(is_array($this->selectedVendor)) {
                foreach ($this->selectedVendor as $key => $supp_org_id) {
                    $this->selectedVendor[$key] = (int) $supp_org_id;
                }
                $this->selectedVendor = implode(', ', $this->selectedVendor);
            } else {
                $this->selectedVendor = (int) $this->selectedVendor;
            }
            $where .= ' AND `org`.id IN (' .$this->selectedVendor. ') ';
        }

        if(!empty($this->searchCategory)) {
            if(is_array($this->searchCategory)) {
                foreach ($this->searchCategory as $key => $category_id) {
                    $this->searchCategory[$key] = (int) $category_id;
                }
                $this->searchCategory = implode(', ', $this->searchCategory);
            } else {
                $this->searchCategory = (int) $this->searchCategory;
            }
            $where .= ' AND category_id IN (' .$this->searchCategory. ') ';
        }

        if(!empty($this->searchPrice)) {
            if(isset($this->searchPrice['start'])) {
                $params_sql[':price_start'] = $this->searchPrice['start'];
                $where_all .= ' AND price >= :price_start ';
            }
            if(isset($this->searchPrice['end'])) {
                $params_sql[':price_end'] = $this->searchPrice['end'];
                $where_all .= ' AND price <= :price_end ';
            }
        }

        $sql = "
        SELECT DISTINCT * FROM (
           SELECT 
              " . implode(',', $fieldsCBG) . "
           FROM `catalog_base_goods` `cbg`
             LEFT JOIN `organization` `org` ON cbg.supp_org_id = org.id
             LEFT JOIN `catalog` `cat` ON cbg.cat_id = cat.id
             LEFT JOIN `currency` `curr` ON cat.currency_id = curr.id
           WHERE
           cat_id IN (" . $this->catalogs . ")
           ".$where."
           AND (cbg.status = 1 AND cbg.deleted = 0)
        UNION ALL
          SELECT 
          " . implode(',', $fieldsCG) . "
          FROM `catalog_goods` `cg`
           LEFT JOIN `catalog_base_goods` `cbg` ON cg.base_goods_id = cbg.id
           LEFT JOIN `organization` `org` ON cbg.supp_org_id = org.id
           LEFT JOIN `catalog` `cat` ON cg.cat_id = cat.id
           LEFT JOIN `currency` `curr` ON cat.currency_id = curr.id
          WHERE 
          cg.cat_id IN (" . $this->catalogs . ")
          ".$where."
          AND (cbg.status = 1 AND cbg.deleted = 0)
        ) as c WHERE id != 0 ".$where_all;

        $query = Yii::$app->db->createCommand($sql);

        $dataProvider = new SqlDataProvider([
            'sql' => $query->sql,
            'params' => $params_sql,
            'pagination' => [
                'page' => isset($params['page']) ? ($params['page']-1) : 0,
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : null,
                'params' => [
                    'sort' => isset($params['sort']) ? $params['sort'] : 'product',
                ]
            ],
            'sort' => [
                'attributes' => [
                    'product' => [
                        'asc' => ['alf_cyr' => SORT_DESC, 'product' => SORT_ASC],
                        'desc' => ['alf_cyr' => SORT_ASC, 'product' => SORT_DESC],
                        'default' => SORT_ASC
                    ],
                    'price',
                    'units',
                    'article',
                    'name',
                    'c_article_1',
                    'c_article',
                    'i'
                ],
                'defaultOrder' => [
                    'i' => SORT_DESC,
                    'c_article_1' => SORT_ASC,
                    'c_article' => SORT_ASC
                ]
            ],
        ]);
        return $dataProvider;
    }

}
