<?php

namespace api\common\models\iiko\search;

use api\common\models\iiko\iikoService;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class iikoServiceSearch extends iikoService
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at','updated_at','is_deleted','user_id','org','fd','td','status_id','is_deleted','code','name','address','phone'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
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
        $query = iikoService::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'name' => $this->name,
            'org' => $this->org,
            'fd' => $this->fd,
            'td' => $this->td,
            'code' => $this->code,
            'status_id' => $this->status_id,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
