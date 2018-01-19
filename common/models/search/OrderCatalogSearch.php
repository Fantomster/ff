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

        $fields = [
            'cbg.id', 'cbg.product', 'cbg.supp_org_id', 'cbg.units', 'cbg.price', 'cbg.cat_id',
            'cbg.article', 'cbg.note', 'cbg.ed', 'curr.symbol', 'org.name',
            "(`cbg`.`article` + 0) AS c_article_1",
            "`cbg`.`article` AS c_article", "`cbg`.`article` REGEXP '^-?[0-9]+$' AS i",
            "`cbg`.`product` REGEXP '^-?[а-яА-Я].*$' AS `alf_cyr`"
        ];

        $union_sql = (new Query())->select($fields)
            ->from('catalog_goods AS cg')
            ->leftJoin('catalog_base_goods AS cbg', 'cg.base_goods_id = cbg.id')
            ->leftJoin('organization AS org', 'cbg.supp_org_id = org.id')
            ->leftJoin('catalog AS cat', 'cg.cat_id = cat.id')
            ->leftJoin('currency AS curr', 'cat.currency_id = curr.id')
            ->where("cg.cat_id IN ($this->catalogs)")
            ->andWhere('(cbg.status = 1) AND (cbg.deleted = 0)');

        $query = (new Query())->select($fields)
            ->from('catalog_base_goods AS cbg')
            ->leftJoin('organization AS org', 'cbg.supp_org_id = org.id')
            ->leftJoin('catalog AS cat', 'cbg.cat_id = cat.id')
            ->leftJoin('currency AS curr', 'cat.currency_id = curr.id')
            ->where("cat_id IN ($this->catalogs)")
            ->andWhere('(cbg.status = 1) AND (cbg.deleted = 0)');

        if(!empty($this->searchString)) {
            $union_sql->andWhere('cbg.product LIKE :searchString OR cbg.article LIKE :searchString', [':searchString' => "%$this->searchString%"]);
            $query->andWhere('cbg.product LIKE :searchString OR cbg.article LIKE :searchString', [':searchString' => "%$this->searchString%"]);
        }

        $query->union($union_sql, true);

        $sort = isset($params['sort']) ? $params['sort'] : '';

        if($sort == 'product') {
            $query->orderBy('`alf_cyr` DESC, `product` ASC');
        }
        if($sort == '-product') {
            $query->orderBy('`alf_cyr` DESC, `product` DESC');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
                'page' => isset($params['page']) ? ($params['page']-1) : 0,
                'params' => [
                    'sort' => isset($params['sort']) ? $params['sort'] : '',
                ]
            ],
            'sort' => [
                'attributes' => [
                    'product',
                    'price',
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
