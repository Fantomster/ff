<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models\search;

use yii\data\ActiveDataProvider;

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
            [['email', 'profile.full_name', 'role.name', 'organization_id', 'searchString'], 'safe'],
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
        $userTable = $user::tableName();
        $profileTable = $profile::tableName();
        $roleTable = $role::tableName();

        $query = $user::find();
        $query->joinWith(['profile' => function ($query) use ($profileTable) {
            $query->from(['profile' => $profileTable]);
        }]);
        $query->joinWith(['role' => function ($query) use ($roleTable) {
            $query->from(['role' => $roleTable]);
        }]);

        // create data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // enable sorting for the related columns
        $addSortAttributes = ["profile.full_name", "role.name"];
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
            ->orFilterWhere(['like', "role.name", $this->searchString]);
        $query->andFilterWhere([
            'status' => $this->status,
            'organization_id' => $this->organization_id,
        ]);

        return $dataProvider;
    }
}
