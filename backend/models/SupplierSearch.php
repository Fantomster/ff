<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;

/**
 * OrganizationSearch represents the model behind the search form about `common\models\Organization`.
 */
class SupplierSearch extends Organization {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'type_id', 'step'], 'integer'],
            [['name', 'city', 'address', 'zip_code', 'phone', 'email', 'website', 'created_at', 'updated_at', 'white_list', 'partnership', 'locality', 'administrative_area_level_1'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params) {
        $query = Organization::find();

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type_id' => $this->type_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'step' => $this->step,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'city', $this->city])
                ->andFilterWhere(['like', 'locality', $this->locality])
                ->andFilterWhere(['like', 'administrative_area_level_1', $this->locality])
                ->andFilterWhere(['like', 'address', $this->address])
                ->andFilterWhere(['like', 'zip_code', $this->zip_code])
                ->andFilterWhere(['like', 'phone', $this->phone])
                ->andFilterWhere(['like', 'email', $this->email])
                ->andFilterWhere(['like', 'website', $this->website])
                ->andFilterWhere(['white_list' => $this->white_list])
                ->andFilterWhere(['partnership' => $this->partnership]);

        return $dataProvider;
    }

}
