<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models\search;

use yii\data\ActiveDataProvider;
use common\models\RelationUserOrganization;

/**
 *  Model for user search form
 */
class UserSearch extends \common\models\User {
    
    public $searchString;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%user}}";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'role_id', 'status'], 'integer'],
            [['email', 'profile.full_name', 'profile.phone', 'role.name', 'organization_id', 'searchString'], 'safe'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), ['organization_id', 'profile.full_name', 'role.name']);
    }
    
    /**
     * Search
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /** @var \common\models\User $user */
        /** @var \common\models\Profile $profile */
        /** @var \common\models\Role $role */

        // get models
        $user = $this->module->model("User");
        $profile = $this->module->model("Profile");
        $role = $this->module->model("Role");
        $organization = $this->module->model("Organization");
        $userTable = $user::tableName();
        $profileTable = $profile::tableName();
        $roleTable = $role::tableName();
        $organizationTable = $organization::tableName();
        $relationUserOrganizationTable = RelationUserOrganization::tableName();

        $query = $user::find();
        $query->joinWith(['profile' => function ($query) use ($profileTable) {
            $query->from(['profile' => $profileTable]);
        }]);
        $query->joinWith(['role' => function ($query) use ($roleTable) {
            $query->from(['role' => $roleTable]);
        }]);
        $query->joinWith(['organization' => function ($query) use ($organizationTable) {
            $query->from(['organization' => $organizationTable]);
        }]);
        $query->joinWith(['relationUserOrganization' => function ($query) use ($relationUserOrganizationTable) {
            $query->from(['relationUserOrganization' => $relationUserOrganizationTable]);
        }]);

        // create data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // enable sorting for the related columns
        $addSortAttributes = ["profile.full_name", "role.name", "profile.phone"];
        foreach ($addSortAttributes as $addSortAttribute) {
            $dataProvider->sort->attributes[$addSortAttribute] = [
                'asc' => [$addSortAttribute => SORT_ASC],
                'desc' => [$addSortAttribute => SORT_DESC],
            ];
        }

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->orFilterWhere(['like', 'email', $this->searchString])
            ->orFilterWhere(['like', "profile.full_name", $this->searchString])
            ->orFilterWhere(['like', "profile.phone", $this->searchString])
            ->orFilterWhere(['like', "role.name", $this->searchString])
            ->orFilterWhere(['user.organization_id' => $this->organization_id])
            ->orFilterWhere(['relationUserOrganization.organization_id' => $this->organization_id]);
        $query->andFilterWhere([
            'status' => $this->status,
        ]);
        return $dataProvider;
    }
}
