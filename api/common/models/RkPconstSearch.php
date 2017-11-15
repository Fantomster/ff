<?php

namespace api\common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\common\models\RkDic;
use common\models\Organization;
use common\models\User;


class RkPconstSearch extends RkPconst
{

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = RkPconst::find();
        
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
            'const_id' => $this->const_id,
            'org' => $this->org,
            'value' => $this->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,


        ]);

        return $dataProvider;
    }
    

}
