<?php

namespace api\common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\common\models\ApiActions;

/**
 * RkAccessSearch represents the model behind the search form about `api\common\models\RkAccess`.
 */
class ApiActionsSearch extends ApiActions
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'session', 'result'], 'integer'],
            [['id', 'action', 'session', 'created', 'result', 'comment', 'ip'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ApiActions::find();

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
            'action' => $this->action,
            'session' => $this->session,
            'created' => $this->created,
            'result' => $this->result,
            'comment' => $this->comment,
            'ip' => $this->ip,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'action', $this->action])
            ->andFilterWhere(['like', 'session', $this->session])
            ->andFilterWhere(['like', 'created', $this->created])
            ->andFilterWhere(['like', 'result', $this->result])
            ->andFilterWhere(['like', 'comment', $this->comment])
            ->andFilterWhere(['like', 'ip', $this->ip]);
        

        return $dataProvider;
    }
}
