<?php

namespace api\common\models\one_s\search;

use api\common\models\iiko\iikoDicconst;
use api\common\models\one_s\OneSDicconst;
use yii\data\ActiveDataProvider;

class OneSDicconstSearch extends OneSDicconst
{
    public function search($params)
    {
        $query = OneSDicconst::find()->andWhere(['is_active' => 1]);

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
