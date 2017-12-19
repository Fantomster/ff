<?php

namespace common\models\search;

use Yii;
use yii\data\SqlDataProvider;

/**
 * Description of FavoriteSearch
 *
 * @author elbabuino
 */
class FavoriteSearch extends \yii\base\Model {

    public $searchString;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['searchString', 'id', 'product', 'order.created_at'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param integer $clientId
     *
     * @return SqlDataProvider
     */
    public function search($params, $clientId) {

        $this->load($params);

        $searchString = "%$this->searchString%";

        $query = "
            SELECT
                cbg.id as cbg_id, cbg.product, cbg.units, cbg.price, cbg.cat_id, org.name, cbg.ed, curr.symbol, cbg.note
            FROM `order_content` AS oc
                LEFT JOIN `order` AS ord ON oc.order_id = ord.id
                LEFT JOIN `catalog_base_goods` AS cbg ON oc.product_id = cbg.id
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                    AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $clientId)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE 
                (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0) 
            GROUP BY cbg.id
            UNION ALL
            (SELECT
                cbg.id as cbg_id, cbg.product, cbg.units, cg.price, cg.cat_id, org.name, cbg.ed, curr.symbol, cbg.note
            FROM `order_content` AS oc
                LEFT JOIN `order` AS ord ON oc.order_id = ord.id
                LEFT JOIN `catalog_base_goods` AS cbg ON oc.product_id = cbg.id
                LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = oc.product_id 
                    AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $clientId)))
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cg.cat_id = cat.id
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE 
                (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0) 
            GROUP BY cbg.id)
        ";

        $query1 = "
            SELECT
                COUNT(DISTINCT cbg.id) 
            FROM `order_content` AS oc
                LEFT JOIN `order` AS ord ON oc.order_id = ord.id
                LEFT JOIN `catalog_base_goods` AS cbg ON oc.product_id = cbg.id
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                    AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $clientId)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE 
                (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0) 
                ";
        $count1 = Yii::$app->db->createCommand($query1, [':searchString' => $searchString])->queryScalar();
        $query2 = "
            SELECT
                COUNT(DISTINCT cbg.id) 
            FROM `order_content` AS oc
                LEFT JOIN `order` AS ord ON oc.order_id = ord.id
                LEFT JOIN `catalog_base_goods` AS cbg ON oc.product_id = cbg.id
                LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = oc.product_id 
                    AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $clientId)))
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cg.cat_id = cat.id
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE 
                (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0) 
                ";
        $count2 = Yii::$app->db->createCommand($query2, [':searchString' => $searchString])->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'params' => [':searchString' => $searchString],
            'totalCount' => $count1 + $count2,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'product',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC
                ]
            ],
        ]);

        return $dataProvider;
    }

}
