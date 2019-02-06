<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\BuisinessInfo;
use common\models\Organization;

/**
 * BuisinessInfoSearch represents the model behind the search form about `common\models\BuisinessInfo`.
 */
class BuisinessInfoSearch extends BuisinessInfo
{

    public $org_name;
    public $org_id;
    public $org_type_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'organization_id'], 'integer'],
            [['org_name', 'org_id', 'org_type_id', 'info', 'created_at', 'updated_at'], 'safe'],
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
        $organizationTable = Organization::tableName();

        $query = BuisinessInfo::find();
        $query->joinWith(['organization']);

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

        $dataProvider->sort->attributes['org_name'] = [
            'asc'  => ["$organizationTable.name" => SORT_ASC],
            'desc' => ["$organizationTable.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['org_id'] = [
            'asc'  => ["$organizationTable.id" => SORT_ASC],
            'desc' => ["$organizationTable.id" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['org_type_id'] = [
            'asc'  => ["$organizationTable.type_id" => SORT_ASC],
            'desc' => ["$organizationTable.type_id" => SORT_DESC],
        ];

        // grid filtering conditions
        $query->andFilterWhere([
            'id'                    => $this->id,
            'organization_id'       => $this->organization_id,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            "$organizationTable.id" => $this->org_id,
        ]);

        $query->andFilterWhere(['like', 'info', $this->info])
            ->andFilterWhere(["$organizationTable.type_id" => $this->org_type_id])
            ->andFilterWhere(['like', "$organizationTable.name", $this->org_name])
            ->andWhere(["$organizationTable.white_list" => 1]);

        return $dataProvider;
    }
}
