<?php

namespace api\common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\common\models\RkSession;

/**
 * RkAccessSearch represents the model behind the search form about `api\common\models\RkAccess`.
 */
class RkSessionSearch extends RkSession
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'fid', 'acc', 'ver', 'status'], 'integer'],
            [['acc', 'cook', 'ip', 'fid', 'fd', 'td', 'extime', 'comment'], 'safe'],
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
        $query = RkSession::find();

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
            'fid' => $this->fid,
            'acc' => $this->acc,
            'fd' => $this->fd,
            'td' => $this->td,
            'ver' => $this->ver,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'ip', $this->ip]);
        //    ->andFilterWhere(['like', 'password', $this->password])
        //    ->andFilterWhere(['like', 'token', $this->token])
        //    ->andFilterWhere(['like', 'lic', $this->lic])
        //    ->andFilterWhere(['like', 'usereq', $this->usereq])
        //    ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
