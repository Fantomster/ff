<?php

namespace backend\models;

use common\models\AmoFields;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CatalogBaseGoodsSearch represents the model behind the search form about `common\models\CatalogBaseGoods`.
 */
class AmoSearch extends AmoFields
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amo_field'], 'string', 'max' => 255],
            [['responsible_user_id', 'pipeline_id'], 'integer'],
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
    public function search($params = null)
    {
        $query = AmoFields::find();

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
        $query->andFilterWhere(['like', 'amo_field', $this->amo_field])
            ->andFilterWhere(['like', 'responsible_user_id', $this->responsible_user_id])
            ->andFilterWhere(['like', 'pipeline_id', $this->pipeline_id]);

        return $dataProvider;
    }
}
