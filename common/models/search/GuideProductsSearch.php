<?php

namespace common\models\search;

use Yii;
use yii\data\SqlDataProvider;

/**
 * Description of GuideProductsSearch
 *
 * @author elbabuino
 */
class GuideProductsSearch extends \yii\base\Model {
    
    public $searchString;
    
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['searchString', 'guide_id', 'cbg_id'], 'safe'],
        ];
    }
    
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param integer $guideId
     * @param integer $clientId
     *
     * @return SqlDataProvider
     */
    public function search($params, $guideId, $clientId) {
        $this->load($params);
        
        $searchString = "%$this->searchString%";

        $query = "
            SELECT gp.id, cbg.id as cbg_id, cbg.product, cbg.units, cbg.price, cbg.cat_id, org.name, cbg.ed, curr.symbol, cbg.note 
            FROM guide_product AS gp
                    LEFT JOIN catalog_base_goods AS cbg ON gp.cbg_id = cbg.id
                    LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                            AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $clientId)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE (gp.guide_id = $guideId)
                    AND (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0) 
            UNION ALL
            (SELECT gp.id, cbg.id as cbg_id, cbg.product, cbg.units, cg.price, cg.cat_id, org.name, cbg.ed, curr.symbol, cbg.note
            FROM guide_product AS gp
                    LEFT JOIN catalog_base_goods AS cbg ON gp.cbg_id = cbg.id
                    LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = gp.cbg_id 
                            AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $clientId)))
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog AS cat ON cg.cat_id = cat.id 
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE (gp.guide_id = $guideId)
                    AND (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0))
                ";

        $query1 = "
            SELECT COUNT(cbg.id)
            FROM guide_product AS gp
                    LEFT JOIN catalog_base_goods AS cbg ON gp.cbg_id = cbg.id
                    LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                            AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = 1)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE (gp.guide_id = 3)
                    AND (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0) 
                ";
        $count1 = Yii::$app->db->createCommand($query1, [':searchString' => $searchString])->queryScalar();
        $query2 = "
            SELECT COUNT(cbg.id)
            FROM guide_product AS gp
                    LEFT JOIN catalog_base_goods AS cbg ON gp.cbg_id = cbg.id
                    LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = gp.cbg_id 
                            AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = 1)))
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog AS cat ON cg.cat_id = cat.id 
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE (gp.guide_id = 3)
                    AND (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0)            
                ";
        $count2 = Yii::$app->db->createCommand($query2, [':searchString' => $searchString])->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'params' => [':searchString' => $searchString],
            'totalCount' => $count1 + $count2,
            'pagination' => false,
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
