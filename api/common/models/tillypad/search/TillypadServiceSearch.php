<?php

namespace api\common\models\tillypad\search;

use api\common\models\tillypad\TillypadService;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use common\helpers\DBNameHelper;

class TillypadServiceSearch extends TillypadService
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'is_deleted', 'user_id', 'org', 'fd', 'td', 'status_id', 'is_deleted', 'code', 'name', 'address', 'phone'], 'safe'],
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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TillypadService::find();
        $dbName = DBNameHelper::getMainName();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        $query->leftJoin($dbName . '.' . Organization::tableName(), 'organization.id = org');

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['status_id' => $this->status_id])
            ->andFilterWhere(['like', 'fd', $this->fd])
            ->andFilterWhere(['like', 'td', $this->td])
            ->andFilterWhere(['like', 'organization.name', $this->org]);

        return $dataProvider;
    }
}
