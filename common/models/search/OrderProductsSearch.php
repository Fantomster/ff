<?php

namespace common\models\search;

use Yii;
use yii\data\SqlDataProvider;

/**
 * Description of GuideProductsSearch
 *
 * @author elbabuino
 */
class OrderProductsSearch extends \yii\base\Model {
    
    public $searchString;
    public $sort;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['searchString', 'cbg_id'], 'safe'],
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
    public function search(array $params, $order) {
        $this->load($params);
        
        $searchString = "%$this->searchString%";



        $query = "
            SELECT cbg.id as cbg_id, cbg.product, cbg.units, cbg.price, cbg.cat_id, org.name, cbg.ed, curr.symbol, cbg.note 
            FROM catalog_base_goods AS cbg
                    LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                            AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $order->client_id)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE (cbg.supp_org_id = $order->vendor_id)
                AND (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0) 
                AND (cbg.id not in (select product_id from order_content where order_id = $order->id))
            UNION ALL
            (
            SELECT cbg.id as cbg_id, cbg.product, cbg.units, cg.price, cg.cat_id, org.name, cbg.ed, curr.symbol, cbg.note
            FROM catalog_base_goods AS cbg
                    LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = cbg.id 
                    AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $order->client_id)))
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog AS cat ON cg.cat_id = cat.id 
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE (cbg.supp_org_id = $order->vendor_id)
                AND (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0)
                AND (cbg.id not in (select product_id from order_content where order_id = $order->id)))
                ";

        $sort = [

        ];
        if(isset($params['sort'])){
            $arr = explode(' ', $params['sort']);
            $query.= " ORDER BY ";
            $query.= str_replace('3', "ASC", str_replace('4', "DESC", $params['sort']));
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'params' => [':searchString' => $searchString],
            //'totalCount' => $count1 + $count2,
            'pagination' => [
                'page' => isset($params['page']) ? ($params['page']-1) : 0,
                'pageSize' => 8,],
            'sort' => $sort,
        ]);
        
        return $dataProvider;
    }
}
