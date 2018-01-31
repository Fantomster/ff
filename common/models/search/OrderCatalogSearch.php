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
    public $catalogs;
    public $client;

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
            'cbg.id', 'cbg.product', 'cbg.supp_org_id', 'cbg.units', 'cbg.price', 'cbg.cat_id',
            'cbg.article', 'cbg.note', 'cbg.ed', 'curr.symbol', 'org.name',
            "(`cbg`.`article` + 0) AS c_article_1",
            "`cbg`.`article` AS c_article", "`cbg`.`article` REGEXP '^-?[0-9]+$' AS i",
            "`cbg`.`product` REGEXP '^-?[а-яА-Я].*$' AS `alf_cyr`"
        ];
        $fieldsCG = [
            'cbg.id', 'cbg.product', 'cbg.supp_org_id', 'cbg.units', 'cg.price', 'cg.cat_id',
            'cbg.article', 'cbg.note', 'cbg.ed', 'curr.symbol', 'org.name',
            "(`cbg`.`article` + 0) AS c_article_1",
            "`cbg`.`article` AS c_article", "`cbg`.`article` REGEXP '^-?[0-9]+$' AS i",
            "`cbg`.`product` REGEXP '^-?[а-яА-Я].*$' AS `alf_cyr`"
        ];

        $where = '';
        $params_sql = [];
        if(!empty($this->searchString)) {
            $where .= 'AND (cbg.product LIKE :searchString OR cbg.article LIKE :searchString)';
            $params_sql[':searchString'] = "%" . $this->searchString . "%";
        }

        if(!empty($this->selectedVendor)) {
            $where .= ' AND `org`.id = :searchVendor ';
            $params_sql[':searchVendor'] = $this->selectedVendor;
        }

        $sql = "
        SELECT * FROM (
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
        ) as c ";

        $query = Yii::$app->db->createCommand($sql);

        $dataProvider = new SqlDataProvider([
            'sql' => $query->sql,
            'params' => $params_sql,
            'pagination' => [
                'page' => isset($params['page']) ? ($params['page']-1) : 0,
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
