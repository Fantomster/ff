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
            'id' => $guideList,
        ]);
        
        // grid filtering conditions
        $query->andFilterWhere(['like', 'product', $this->searchString]);

        return $dataProvider;
    }
}
