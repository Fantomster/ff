<?php

namespace api\common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\common\models\RkProduct;

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
            [['created_at','updated_at','is_deleted','user_id','org','fd','td','status_id','is_deleted','code','name','address','phone'], 'safe'],
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
          //  ->andFilterWhere(['like', 'token', $this->token])
          //  ->andFilterWhere(['like', 'lic', $this->lic])
          //  ->andFilterWhere(['like', 'usereq', $this->usereq])
          //  ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
