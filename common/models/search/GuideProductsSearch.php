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
class GuideProductsSearch extends \yii\base\Model
{

    public $searchString;
    public $vendor_id;
    public $price_from;
    public $price_to;
    public $sort;

    /**
     * @inheritdoc
     */
    public function rules()
    {
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
     * @return ActiveDataProvider
     */
    public function search(array $params, int $guideId, int $clientId): ActiveDataProvider
    {
        $this->load($params);

        //Блокировка продуктов
        $blockedItems = CatalogGoodsBlocked::getBlockedList($clientId);

        $tblGP = \common\models\guides\GuideProduct::tableName();
        $tblCBG = \common\models\CatalogBaseGoods::tableName();
        $tblCG = \common\models\CatalogGoods::tableName();
        $tblRSR = \common\models\RelationSuppRest::tableName();
        $tblOrg = \common\models\Organization::tableName();
        $tblCat = \common\models\Catalog::tableName();
        $tblCurr = \common\models\Currency::tableName();
        
        $subQueryCatIds = (new Query())
                ->select(["cat_id"])
                ->from($tblRSR)
                ->where("supp_org_id = cbg.supp_org_id")
                ->andWhere(["rest_org_id" => $clientId]);
        
        $subQueryCG = (new Query())
                ->select([
                    "id" => "gp.id", 
                    "cbg_id" => "cbg.id", 
                    "product" => "cbg.product", 
                    "units" => "cbg.units", 
                    "price" => "cg.price", 
                    "cat_id" => "cg.cat_id", 
                    "name" => "org.name", 
                    "ed" => "cbg.ed", 
                    "symbol" => "curr.symbol", 
                    "note" => "cbg.note", 
                    "updated_at" => "gp.updated_at", 
                    "price_updated_at" => "cg.updated_at" 
                ])
                ->from(["gp" => $tblGP])
                ->leftJoin(['cbg' => $tblCBG], "gp.cbg_id = cbg.id")
                ->leftJoin(["cg" => $tblCG], "cg.base_goods_id = gp.cbg_id")
                ->leftJoin(["org" => $tblOrg], "cbg.supp_org_id = org.id")
                ->leftJoin(["cat" => $tblCat], "cg.cat_id = cat.id")
                ->leftJoin(["curr" => $tblCurr], "cat.currency_id = curr.id")
                ->where([
                    "and",
                    ["cg.cat_id" => $subQueryCatIds],
                    ["gp.guide_id" => $guideId],
                    ["cbg.status" => 1],
                    ["cbg.deleted" => 0],
                    ["cat.status" => 1]
                ])
                ->andFilterWhere(["like", "cbg.product", $this->searchString])
                ->andFilterWhere(["cbg.supp_org_id" => $this->vendor_id])
                ->andFilterWhere(['>=', 'cg.price', $this->price_from])
                ->andFilterWhere(['<=', 'cg.price', $this->price_to]);
        
        $subQueryCBG = (new Query())
                ->select([
                    "id" => "gp.id", 
                    "cbg_id" => "cbg.id", 
                    "product" => "cbg.product", 
                    "units" => "cbg.units", 
                    "price" => "cbg.price", 
                    "cat_id" => "cbg.cat_id", 
                    "name" => "org.name", 
                    "ed" => "cbg.ed", 
                    "symbol" => "curr.symbol", 
                    "note" => "cbg.note", 
                    "updated_at" => "gp.updated_at", 
                    "price_updated_at" => "cbg.updated_at" 
                ])
                ->from(["gp" => $tblGP])
                ->leftJoin(['cbg' => $tblCBG], "gp.cbg_id = cbg.id")
                ->leftJoin(["org" => $tblOrg], "cbg.supp_org_id = org.id")
                ->leftJoin(["cat" => $tblCat], "cbg.cat_id = cat.id")
                ->leftJoin(["curr" => $tblCurr], "cat.currency_id = curr.id")
                ->where([
                    "and",
                    ["cbg.cat_id" => $subQueryCatIds],
                    ["gp.guide_id" => $guideId],
                    ["cbg.status" => 1],
                    ["cbg.deleted" => 0],
                    ["cat.status" => 1]
                ])
                ->andFilterWhere(["like", "cbg.product", $this->searchString])
                ->andFilterWhere(["cbg.supp_org_id" => $this->vendor_id])
                ->andFilterWhere(['>=', 'cbg.price', $this->price_from])
                ->andFilterWhere(['<=', 'cbg.price', $this->price_to]);
        
        $query = (new Query())
                ->from(["c" => $subQueryCG->union($subQueryCBG, true)])
                ->distinct()
                ->where(["not in", "c.cbg_id", $blockedItems])
                ->groupBy(["c.id"]);
        
        if (isset($params['limit'])) {
            $query->limit($params['limit']);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
