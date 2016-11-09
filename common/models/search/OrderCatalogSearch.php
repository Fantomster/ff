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
        
        $searchString = "%$this->searchString%";

        $query = "SELECT cbg.id, cbg.product, cbg.supp_org_id, cbg.units, cbg.price, cbg.cat_id, org.name, cbg.article, cbg.note, cbg.ed FROM "
                . "catalog_base_goods AS cbg LEFT OUTER JOIN organization AS org ON cbg.supp_org_id = org.id "
                . "WHERE cat_id IN ($this->catalogs) AND (cbg.product LIKE :searchString OR cbg.article LIKE :searchString) "
                . "AND (cbg.status = 1) AND (cbg.deleted = 0) "
                . "UNION ALL (SELECT cbg.id, cbg.product, cbg.supp_org_id, cbg.units, cg.price, cg.cat_id, org.name, cbg.article, cbg.note, cbg.ed FROM "
                . "catalog_goods AS cg LEFT OUTER JOIN catalog_base_goods AS cbg ON cg.base_goods_id = cbg.id "
                . "LEFT OUTER JOIN organization AS org ON cbg.supp_org_id = org.id "
                . "WHERE cg.cat_id IN ($this->catalogs) AND (cbg.product LIKE :searchString OR cbg.article LIKE :searchString) "
                . "AND (cbg.status = 1) AND (cbg.deleted = 0))";

        $query1 = "SELECT COUNT(cbg.id) FROM "
                . "catalog_base_goods AS cbg LEFT OUTER JOIN organization AS org ON cbg.supp_org_id = org.id "
                . "WHERE cat_id IN ($this->catalogs) AND (cbg.product LIKE :searchString OR cbg.article LIKE :searchString) "
                . "AND (cbg.status = 1) AND (cbg.deleted = 0)";
        $count1 = Yii::$app->db->createCommand($query1, [':searchString' => $searchString])->queryScalar();
        $query2 = "SELECT COUNT(cbg.id) FROM "
                . "catalog_goods AS cg LEFT OUTER JOIN catalog_base_goods AS cbg ON cg.base_goods_id = cbg.id "
                . "LEFT OUTER JOIN organization AS org ON cbg.supp_org_id = org.id "
                . "WHERE cg.cat_id IN ($this->catalogs) AND (cbg.product LIKE :searchString OR cbg.article LIKE :searchString)"
                . "AND (cbg.status = 1) AND (cbg.deleted = 0)";
        $count2 = Yii::$app->db->createCommand($query2, [':searchString' => $searchString])->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'params' => [':searchString' => $searchString],
            'totalCount' => $count1 + $count2,
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
