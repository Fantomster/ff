<?php

namespace common\models\search;

use common\models\CatalogBaseGoods;
use common\models\CatalogGoodsBlocked;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\Expression;

/**
 *  Model for order catalog search form
 */
class OrderCatalogSearch extends \yii\base\Model
{

    public $searchString;
    public $selectedCategory;
    public $selectedVendor;
    public $searchPrice;
    public $catalogs;
    public $client;
    public $searchCategory;
    public $product_block = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product', 'price', 'searchString', 'selectedCategory', 'selectedVendor'], 'safe'],
        ];
    }

    /**
     * Search
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->load($params);

        $tblCBG   = \common\models\CatalogBaseGoods::tableName();
        $tblCG    = \common\models\CatalogGoods::tableName();
        $tblOrg   = \common\models\Organization::tableName();
        $tblCat   = \common\models\Catalog::tableName();
        $tblCurr  = \common\models\Currency::tableName();
        $catalogs = explode(',', $this->catalogs);
        $blockedList = CatalogGoodsBlocked::getBlockedList($this->client->id);
        
        $selectedVendor = ($this->selectedVendor == 0) ? null : $this->selectedVendor;
            
        $subQueryCG = (new Query())
                ->select([
                    "id"                   => "cbg.id",
                    "product"              => "cbg.product",
                    "supp_org_id"          => "cbg.supp_org_id",
                    "units"                => "cbg.units",
                    "price"                => "cg.price",
                    "cat_id"               => "cg.cat_id",
                    "category_id"          => "cbg.category_id",
                    "article"              => "cbg.article",
                    "note"                 => "cbg.note",
                    "ed"                   => "cbg.ed",
                    "symbol"               => "curr.symbol",
                    "name"                 => "org.name",
                    "c_article_1"          => new Expression("(cbg.article + 0)"),
                    "c_article"            => "cbg.article",
                    "i"                    => new Expression("cbg.article REGEXP '^-?[0-9]+$'"),
                    "alf_cyr"              => new Expression("cbg.product REGEXP '^-?[а-яА-Я].*$'"),
                    "updated_at"           => new Expression("coalesce(cg.updated_at, cbg.updated_at)"),
                    "currency_id"          => "curr.id",
                    "edi_supplier_article" => "cbg.edi_supplier_article"
                ])
                ->from(["cg" => $tblCG])
                ->leftJoin(['cbg' => $tblCBG], "cg.base_goods_id = cbg.id")
                ->leftJoin(["org" => $tblOrg], "cbg.supp_org_id = org.id")
                ->leftJoin(["cat" => $tblCat], "cg.cat_id = cat.id")
                ->leftJoin(["curr" => $tblCurr], "cat.currency_id = curr.id")
                ->where([
                    "and",
                    ["cat.id" => $catalogs],
                    ["cbg.status" => 1],
                    ["cbg.deleted" => 0],
                ])
                ->andFilterWhere(["like", "cbg.product", $this->searchString])
                ->andFilterWhere(["cbg.supp_org_id" => $selectedVendor])
                ->andFilterWhere(['>=', 'cg.price', $this->searchPrice['from']])
                ->andFilterWhere(['<=', 'cg.price', $this->searchPrice['to']]);

        $subQueryCBG = (new Query())
                ->select([
                    "id"                   => "cbg.id",
                    "product"              => "cbg.product",
                    "supp_org_id"          => "cbg.supp_org_id",
                    "units"                => "cbg.units",
                    "price"                => "cbg.price",
                    "cat_id"               => "cbg.cat_id",
                    "category_id"          => "cbg.category_id",
                    "article"              => "cbg.article",
                    "note"                 => "cbg.note",
                    "ed"                   => "cbg.ed",
                    "symbol"               => "curr.symbol",
                    "name"                 => "org.name",
                    "c_article_1"          => new Expression("(cbg.article + 0)"),
                    "c_article"            => "cbg.article",
                    "i"                    => new Expression("cbg.article REGEXP '^-?[0-9]+$'"),
                    "alf_cyr"              => new Expression("cbg.product REGEXP '^-?[а-яА-Я].*$'"),
                    "updated_at"           => "cbg.updated_at",
                    "currency_id"          => "curr.id",
                    "edi_supplier_article" => "cbg.edi_supplier_article"
                ])
                ->from(["cbg" => $tblCBG])
                ->leftJoin(["org" => $tblOrg], "cbg.supp_org_id = org.id")
                ->leftJoin(["cat" => $tblCat], "cbg.cat_id = cat.id")
                ->leftJoin(["curr" => $tblCurr], "cat.currency_id = curr.id")
                ->where([
                    "and",
                    ["cat.id" => $catalogs],
                    ["cbg.status" => 1],
                    ["cbg.deleted" => 0],
                ])
                ->andFilterWhere(["like", "cbg.product", $this->searchString])
                ->andFilterWhere(["cbg.supp_org_id" => $selectedVendor])
                ->andFilterWhere(['>=', 'cbg.price', $this->searchPrice['from']])
                ->andFilterWhere(['<=', 'cbg.price', $this->searchPrice['to']]);

        $query = (new Query())
                ->from(["c" => $subQueryCG->union($subQueryCBG, true)])
                ->distinct()
                ->where(["not in", "c.id", $blockedList])
                ->groupBy(["c.id"]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'page'     => isset($params['page']) ? ($params['page'] - 1) : 0,
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : null,
                'params'   => [
                    'sort' => isset($params['sort']) ? $params['sort'] : 'product',
                ]
            ],
            'sort'       => [
                'attributes'   => [
                    'product' => [
                        'asc'     => ['alf_cyr' => SORT_DESC, 'product' => SORT_ASC],
                        'desc'    => ['alf_cyr' => SORT_ASC, 'product' => SORT_DESC],
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
                    'product'     => SORT_ASC,
                    'c_article_1' => SORT_ASC,
                    'c_article'   => SORT_ASC
                ]
            ],
        ]);
        return $dataProvider;
    }

}
