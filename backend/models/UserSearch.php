<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;
use common\models\Role;
use common\models\Profile;
use common\models\Organization;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class UserSearch extends User {

    public $role;
    public $profile;
    public $organization;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return "{{%user}}";
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'status', 'organization_id'], 'integer'],
            [['email', 'profile', 'role', 'logged_in_ip', 'logged_in_at', 'created_ip', 'created_at', 'updated_at', 'organization'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
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
    public function search($params) {
        $query = User::find();

        $userTable = User::tableName();
        $profileTable = Profile::tableName();
        $roleTable = Role::tableName();
        $organizationTable = Organization::tableName();

        $query = User::find();
        $query->joinWith(['role', 'profile', 'organization']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $dataProvider->sort->attributes['role'] = [
            'asc' => ["$roleTable.name" => SORT_ASC],
            'desc' => ["$roleTable.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['profile'] = [
            'asc' => ["$profileTable.full_name" => SORT_ASC],
            'desc' => ["$profileTable.full_name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['organization'] = [
            'asc' => ["$organizationTable.name" => SORT_ASC],
            'desc' => ["$organizationTable.name" => SORT_DESC],
        ];

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'logged_in_at' => $this->logged_in_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'organization_id' => $this->organization_id,
        ]);

        $query->andFilterWhere(['like', 'email', $this->email])
                ->andFilterWhere(['like', 'logged_in_ip', $this->logged_in_ip])
                ->andFilterWhere(['like', 'created_ip', $this->created_ip])
                ->andFilterWhere(['like', "$roleTable.name", $this->role])
                ->andFilterWhere(['like', "$organizationTable.name", $this->organization])
                ->andFilterWhere(['like', "$profileTable.full_name", $this->profile]);

        return $dataProvider;
    }

}
