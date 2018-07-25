<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Journal;

/**
 * JournalSearch represents the model behind the search form of `common\models\Journal`.
 */
class JournalSearch extends Journal
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'service_id', 'user_id', 'organization_id'], 'integer'],
            [['operation_code', 'response', 'log_guide', 'type', 'created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
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
    public function search($params)
    {
        $query = Journal::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'service_id' => $this->service_id,
            'user_id' => $this->user_id,
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'operation_code', $this->operation_code])
            ->andFilterWhere(['like', 'response', $this->response])
            ->andFilterWhere(['like', 'log_guide', $this->log_guide])
            ->andFilterWhere(['like', 'type', $this->type]);

        return $dataProvider;
    }
}