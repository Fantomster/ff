<?php

namespace api\common\models\iiko\search;

use api\common\models\iiko\iikoDicconst;
use yii\data\ActiveDataProvider;

class iikoDicconstSearch extends iikoDicconst
{
    public function search($params)
    {
        $query = iikoDicconst::find()->andWhere(['is_active' => 1]);

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
            'denom' => $this->denom,
            'def_value' => $this->def_value,

        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
