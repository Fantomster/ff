<?php

namespace api\common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * RkAccessSearch represents the model behind the search form about `api\common\models\RkAccess`.
 */
class RkServiceSearch extends RkService
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //    [['org','fd','td','object_id','status_id'], 'required'],
            //    [['id','fid','org','ver'], 'integer'],
            [['id', 'last_active', 'created_at', 'updated_at', 'is_deleted', 'user_id', 'org', 'fd', 'td', 'status_id', 'is_deleted', 'code', 'name', 'address', 'phone'], 'safe'],
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
        $query = RkService::find();

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
        $query->andFilterWhere(['status_id' => $this->status_id])
            ->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'td', $this->td])
            ->andFilterWhere(['like', 'last_active', $this->last_active]);

        return $dataProvider;
    }
}
