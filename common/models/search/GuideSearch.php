<?php

namespace common\models\search;

use Yii;
use common\models\guides\Guide;
use yii\data\ActiveDataProvider;

/**
 * Description of GuideSearch
 *
 * @author elbabuino
 */
class GuideSearch extends Guide {
    
    public $searchString;
    
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['searchString', 'client_id', 'name', 'updated_at'], 'safe'],
        ];
    }
    
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $client_id) {
        $query = Guide::find();

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
            'client_id' => $client_id,
            'type' => Guide::TYPE_GUIDE,
        ]);
        
        // grid filtering conditions
        $query->andFilterWhere(['like', 'name', $this->searchString]);

        return $dataProvider;
    }
}
