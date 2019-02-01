<?php

namespace common\models\search;

use common\models\CatalogGoodsBlocked;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;

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
            [['searchString', 'cbg_id', 'sort'], 'safe'],
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
        
        $blockedList = CatalogGoodsBlocked::getBlockedList($order->client->id);

        $tblCBG = \common\models\CatalogBaseGoods::tableName();
        $tblCG = \common\models\CatalogGoods::tableName();
        $tblRSR = \common\models\RelationSuppRest::tableName();
        $tblOrg = \common\models\Organization::tableName();
        $tblCat = \common\models\Catalog::tableName();
        $tblCurr = \common\models\Currency::tableName();
        $tblOrdC = \common\models\OrderContent::tableName();
        
        $subQueryOrderContent = (new Query())
                ->select(["product_id"])
                ->from($tblOrdC)
                ->where(["order_id" => $order->id]);
        
        $subQueryCG = (new Query())
                ->select([
                    "cbg_id" => "cbg.id", 
                    "product" => "cbg.product", 
                    "units" => "cbg.units", 
                    "price" => "cg.price", 
                    "cat_id" => "cg.cat_id", 
                    "name" => "org.name", 
                    "ed" => "cbg.ed", 
                    "symbol" => "curr.symbol", 
                    "note" => "cbg.note", 
                ])
                ->from(['cbg' => $tblCBG])
                ->leftJoin(["cg" => $tblCG], "cg.base_goods_id = cbg.id")
                ->leftJoin(["org" => $tblOrg], "cbg.supp_org_id = org.id")
                ->leftJoin(["cat" => $tblCat], "cg.cat_id = cat.id")
                ->leftJoin(["curr" => $tblCurr], "cat.currency_id = curr.id")
                ->where([
                    "and",
                    ["cbg.supp_org_id" => $order->vendor_id],
                    ["cbg.status" => 1],
                    ["cbg.deleted" => 0],
                    ["not in", "cbg.id", $blockedList],
                    ["not in", "cbg.id", $subQueryOrderContent],
                ])
                ->andFilterWhere(["like", "cbg.product", $this->searchString]);
        
        $subQueryCBG = (new Query())
                ->select([
                    "cbg_id" => "cbg.id", 
                    "product" => "cbg.product", 
                    "units" => "cbg.units", 
                    "price" => "cbg.price", 
                    "cat_id" => "cbg.cat_id", 
                    "name" => "org.name", 
                    "ed" => "cbg.ed", 
                    "symbol" => "curr.symbol", 
                    "note" => "cbg.note", 
                ])
                ->from(['cbg' => $tblCBG])
                ->leftJoin(["org" => $tblOrg], "cbg.supp_org_id = org.id")
                ->leftJoin(["cat" => $tblCat], "cbg.cat_id = cat.id")
                ->leftJoin(["curr" => $tblCurr], "cat.currency_id = curr.id")
                ->where([
                    "and",
                    ["cbg.supp_org_id" => $order->vendor_id],
                    ["cbg.status" => 1],
                    ["cbg.deleted" => 0],
                    ["not in", "cbg.id", $blockedList],
                    ["not in", "cbg.id", $subQueryOrderContent],
                ])
                ->andFilterWhere(["like", "cbg.product", $this->searchString]);
        
        $query = (new Query())
                ->from(["c" => $subQueryCG->union($subQueryCBG, true)]);
        
        if (isset($params['limit'])) {
            $query->limit($params['limit']);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 8,
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
        
        /*
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
                AND (cbg.id not in ($blockedItems))
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
                AND (cbg.id not in (select product_id from order_content where order_id = $order->id))
                AND (cbg.id not in ($blockedItems)))
                ";

        $sort = [

        ];
//        if(isset($params['sort'])){
//            $arr = explode(' ', $params['sort']);
//            $query.= " ORDER BY ";
//            $query.= str_replace('3', "ASC", str_replace('4', "DESC", $params['sort']));
//        } сотонизмъ, переделать опосля, пока сортировка отключена

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'params' => [':searchString' => $searchString],
            //'totalCount' => $count1 + $count2,
            'pagination' => [
                'page' => isset($params['page']) ? ($params['page']-1) : 0,
                'pageSize' => 8,],
            'sort' => $sort,
        ]);
        
        return $dataProvider;*/
    }
}
