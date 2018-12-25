<?php

namespace common\models;

use common\helpers\DBNameHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * IntegrationSettingChangeSearch represents the model behind the search form of
 * `common\models\IntegrationSettingChange`.
 */
class IntegrationSettingChangeSearch extends IntegrationSettingChange
{
    public $org_name;
    public $setting_name;
    public $setting_comment;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'org_id', 'integration_setting_id', 'changed_user_id', 'confirmed_user_id', 'is_active'], 'integer'],
            [['org_name', 'setting_name', 'setting_comment'], 'string'],
            [['old_value', 'new_value', 'created_at', 'updated_at', 'confirmed_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = IntegrationSettingChange::find()
            ->from(['isc' => IntegrationSettingChange::tableName()])
            ->where(['isc.is_active' => true])
            ->orderBy(['created_at' => SORT_ASC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        $query->joinWith(['organization' => function ($query) {
            return $query->from(['org' => DBNameHelper::getMainName() . '.' . Organization::tableName()]);
        }], true);
        $query->joinWith(['integrationSetting' => function ($query) {
            /** @var Model $query */
            return $query->from(['setting' => IntegrationSetting::tableName()]);
        }], true);

        $dataProvider->sort->attributes['org_name'] = [
            'asc'  => ["org.name" => SORT_ASC],
            'desc' => ["org.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['setting_name'] = [
            'asc'  => ["setting.name" => SORT_ASC],
            'desc' => ["setting.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['setting_comment'] = [
            'asc'  => ["setting.comment" => SORT_ASC],
            'desc' => ["setting.comment" => SORT_DESC],
        ];

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'old_value', $this->old_value])
            ->andFilterWhere(['like', 'new_value', $this->new_value])
            ->andFilterWhere(['like', 'org.name', $this->org_name])
            ->andFilterWhere(['like', 'setting.name', $this->setting_name])
            ->andFilterWhere(['like', 'setting.comment', $this->setting_comment]);

        return $dataProvider;
    }
}
