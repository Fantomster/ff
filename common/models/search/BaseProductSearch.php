<?php

namespace common\models\search;

use common\models\CatalogBaseGoods;
use yii\data\ActiveDataProvider;

/**
 * Description of BaseProductSearch
 *
 * @author elbabuino
 */
class BaseProductSearch extends \common\models\CatalogBaseGoods {
    public $searchString;
    public $sort;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['searchString', 'id', 'product', 'supp_org_id'], 'safe'],
        ];
    }
    
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param array $guideList
     *
     * @return ActiveDataProvider
     */
    public function search($params, $guideList) {
        $query = CatalogBaseGoods::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if(!$params['show_sorting']){
            $query->where([
                'id' => $guideList,
            ]);

        }else{
            $sort = ['product' => SORT_ASC];

            if (isset($params['sort'])){
                $arr = explode(' ', $params['sort']);
                if(isset($arr[1])){
                    $sort = [str_replace('id', 'guide_product.id', $arr[0]) => (int)$arr[1]];
                }
            }

            $query->leftJoin('guide_product', 'guide_product.cbg_id = catalog_base_goods.id');

            $query->where([
                'catalog_base_goods.id' => $guideList,
                'guide_product.guide_id' => $params['guide_id']
            ]);

            $query->orderBy($sort);

        }

        $query->andFilterWhere(['like', 'product', $this->searchString]);

        return $dataProvider;
    }
}
