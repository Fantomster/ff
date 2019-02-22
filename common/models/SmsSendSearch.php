<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SmsSendSearch represents the model behind the search form about `common\models\SmsSend`.
 */
class SmsSendSearch extends SmsSend
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status_id'], 'integer'],
            [['sms_id', 'text', 'target', 'created_at', 'updated_at', 'provider'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SmsSend::find();

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
            'id'         => $this->id,
            'status_id'  => $this->status_id,
            'updated_at' => $this->updated_at,
        ]);

        if (isset($this->created_at) && !empty($this->created_at)) {
            $query->andFilterWhere(['>=', 'created_at', date('Y-m-d', strtotime($this->created_at)) . ' 00:00:00'])
                ->andFilterWhere(['<=', 'created_at', date('Y-m-d', strtotime($this->created_at)) . ' 23:59:59']);
        }

        $query->andFilterWhere(['like', 'sms_id', $this->sms_id])
            ->andFilterWhere(['like', 'text', $this->text])
            ->andFilterWhere(['like', 'target', $this->target])
            ->andFilterWhere(['like', 'provider', $this->provider]);

        return $dataProvider;
    }
}
