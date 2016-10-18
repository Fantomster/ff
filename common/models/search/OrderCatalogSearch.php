<?php

namespace common\models\search;

use Yii;
use yii\data\SqlDataProvider;

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
        
        $this->searchString = \yii\helpers\HtmlPurifier::process($this->searchString);

        $query = "SELECT cbg.id, cbg.product, cbg.supp_org_id, cbg.units, cbg.price, cbg.cat_id, org.name, cbg.article FROM "
                . "catalog_base_goods AS cbg LEFT OUTER JOIN organization AS org ON cbg.supp_org_id = org.id "
                . "WHERE cat_id IN ($this->catalogs) AND (cbg.product LIKE '%$this->searchString%' OR cbg.article LIKE '%$this->searchString%') "
                . "AND (cbg.status = 1) AND (cbg.deleted = 0) "
                . "UNION ALL (SELECT cbg.id, cbg.product, cbg.supp_org_id, cbg.units, cg.price, cg.cat_id, org.name, cbg.article FROM "
                . "catalog_goods AS cg LEFT OUTER JOIN catalog_base_goods AS cbg ON cg.base_goods_id = cbg.id "
                . "LEFT OUTER JOIN organization AS org ON cbg.supp_org_id = org.id "
                . "WHERE cg.cat_id IN ($this->catalogs) AND (cbg.product LIKE '%$this->searchString%' OR cbg.article LIKE '%$this->searchString%')"
                . "AND (cbg.status = 1) AND (cbg.deleted = 0))";

        $count = Yii::$app->db->createCommand($query)->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'product',
                    'price',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC
                    ]
            ],
        ]);
        return $dataProvider;
    }

}
