<?php

namespace common\models\search;

use Yii;
use common\models\guides\Guide;
use common\models\guides\GuideProduct;
use common\models\CatalogBaseGoods;
use yii\data\ActiveDataProvider;

/**
 * Description of GuideProductsSearch
 *
 * @author elbabuino
 */
class GuideProductsSearch extends GuideProduct {
    
    public $searchString;
    public $name;
    
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['searchString', 'guide_id', 'cbg_id', 'name'], 'safe'],
        ];
    }
    
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param integer $guideId
     *
     * @return ActiveDataProvider
     */
    public function search($params, $guideId) {
        $query = GuideProduct::find();
        $query->joinWith(['baseProduct']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->where([
            'guide_id' => $guideId,
        ]);
        
        // grid filtering conditions
        $query->andFilterWhere(['like', 'catalog_base_goods.product', $this->searchString]);

        return $dataProvider;
    }
}
