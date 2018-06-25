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
    public $full_name;
    public $phone;
    public $org_name;
    public $org_type_id;
    public $sms_allow;
    public $gender;
    public $email_allow;
    public $email;

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
            [['id', 'status', 'organization_id', 'sms_allow', 'gender', 'email_allow'], 'integer'],
            [['email', 'full_name', 'phone', 'role', 'logged_in_ip', 'logged_in_at', 'created_ip', 'created_at', 'updated_at', 'org_name', 'org_type_id'], 'safe'],
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
    public function search($params, $role_id = null) {
        $query = User::find();

        $userTable = User::tableName();
        $profileTable = Profile::tableName();
        $roleTable = Role::tableName();
        $organizationTable = Organization::tableName();
        $jobTable = 'jobs';

        $query = User::find();
        $query->joinWith(['role', 'profile', 'organization']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $dataProvider->sort->attributes['role'] = [
            'asc' => ["$roleTable.name" => SORT_ASC],
            'desc' => ["$roleTable.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['full_name'] = [
            'asc' => ["$profileTable.full_name" => SORT_ASC],
            'desc' => ["$profileTable.full_name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['phone'] = [
            'asc' => ["$profileTable.phone" => SORT_ASC],
            'desc' => ["$profileTable.phone" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['org_name'] = [
            'asc' => ["$organizationTable.name" => SORT_ASC],
            'desc' => ["$organizationTable.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['org_type_id'] = [
            'asc' => ["$organizationTable.type_id" => SORT_ASC],
            'desc' => ["$organizationTable.type_id" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['gender'] = [
            'asc' => ["$profileTable.gender" => SORT_ASC],
            'desc' => ["$profileTable.gender" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['sms_allow'] = [
            'asc' => ["$profileTable.sms_allow" => SORT_ASC],
            'desc' => ["$profileTable.sms_allow" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['email_allow'] = [
            'asc' => ["$profileTable.email_allow" => SORT_ASC],
            'desc' => ["$profileTable.email_allow" => SORT_DESC],
        ];

        // grid filtering conditions
        $query->andFilterWhere([
            $userTable.'.id' => $this->id,
            'status' => $this->status,
            'logged_in_at' => $this->logged_in_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'organization_id' => $this->organization_id,
            'role_id' => $role_id,
        ]);

        $query->andFilterWhere(['like', 'user.email', $this->email])
                ->andFilterWhere(['like', 'logged_in_ip', $this->logged_in_ip])
                ->andFilterWhere(['like', 'created_ip', $this->created_ip])
                ->andFilterWhere(['like', "$roleTable.name", $this->role])
                ->andFilterWhere(['like', "$organizationTable.name", $this->org_name])
                ->andFilterWhere(['like', "$organizationTable.type_id", $this->org_type_id])
                ->andFilterWhere(['like', "$profileTable.full_name", $this->full_name])
                ->andFilterWhere(['like', "$profileTable.sms_allow", $this->sms_allow])
                ->andFilterWhere(['like', "$profileTable.phone", $this->phone])
                ->andFilterWhere(['like', "$profileTable.gender", $this->gender]);

        return $dataProvider;
    }

}
