<?php

namespace common\models\search;

use Yii;
use common\models\guides\GuideProduct;
use common\models\CatalogBaseGoods;

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
     *
     * @return ActiveDataProvider
     */
    public function search($params, $guide_id) {
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
            'guide_id' => $guide_id,
            'type' => Guide::TYPE_GUIDE,
        ]);
        
        // grid filtering conditions
        $query->andFilterWhere(['like', 'name', $this->searchString]);

        return $dataProvider;
    }
}
