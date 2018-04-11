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
    public $vendor_id;
    public $price_from;
    public $price_to;
    public $sort;

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
    public function search(array $params, int $guideId, int $clientId) {
        $this->load($params);
        
        $searchString = "%$this->searchString%";

        $where = [];

        if($this->vendor_id) {
            $where[] = ['cbg.supp_org_id', '=', $this->vendor_id];
        }

        if($this->price_from) {
            $where[] = ['cbg.price', '>=', $this->price_from];
        }

        if($this->price_to) {
            $where[] = ['cbg.price', '<=', $this->price_to];
        }

        if(!empty($where)) {
            $s = '';
            foreach ($where as $field => $condition) {
                $s .= $condition[0] . ' ' . $condition[1] . ' ' . $condition[2] . ' AND ';
            }
            $where = trim($s);
        } else {
            $where = '';
        }

        $query = "
            SELECT gp.id, cbg.id as cbg_id, cbg.product, cbg.units, cbg.price, cbg.cat_id, org.name, cbg.ed, curr.symbol, cbg.note 
            FROM guide_product AS gp
                    LEFT JOIN catalog_base_goods AS cbg ON gp.cbg_id = cbg.id
                    LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                            AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $clientId)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE ($where gp.guide_id = $guideId)
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
            WHERE ($where gp.guide_id = $guideId)
                    AND (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0))
                ";

        $sort = [

        ];
        if(isset($params['sort'])){
            $arr = explode(' ', $params['sort']);
            $query.= " ORDER BY ";
            if($params['sort'] == ''){
                $query.= " product ASC";
            }else{
                $query.= str_replace('3', "ASC", str_replace('4', "DESC", $params['sort']));
            }
        }else{
            $query.= " ORDER BY product ASC";
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'params' => [':searchString' => $searchString],
            //'totalCount' => $count1 + $count2,
            'pagination' => false,
            'sort' => $sort,
        ]);
        
        return $dataProvider;
    }
}
