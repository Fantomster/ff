<?php

namespace backend\models;

use common\models\CountryVat;
use common\models\vetis\VetisCountry;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * VatsSearch represents the model behind the search form about `common\models\CountryVat`.
 */
class VatsSearch extends CountryVat
{
    public $country_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'vats'], 'required'],
            [['id', 'uuid', 'created_by_id', 'updated_by_id'], 'integer'],
            [['created_at', 'updated_at', 'country_name'], 'safe'],
            [['uuid', 'vats'], 'string']
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
    public function search($params = null)
    {
        $this->load($params);

        $countryVatTable = CountryVat::tableName();
        $countryTable = VetisCountry::tableName();

        $query = CountryVat::find()
            ->joinWith('country', true)
            ->andFilterWhere(['like', 'vats', $this->vats])
            ->andFilterWhere(['like', "$countryTable.name", $this->country_name])
            ->orderBy(["$countryTable.name" => SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes' => [
                    'country_name' => [
                        'asc'  => ["$countryTable.name" => SORT_ASC],
                        'desc' => ["$countryTable.name" => SORT_DESC],
                    ],
                    'vats'         => [
                        'asc'  => ["$countryVatTable.vats" => SORT_ASC],
                        'desc' => ["$countryVatTable.vats" => SORT_DESC],
                    ],
                ],
            ]
        ]);

        return $dataProvider;
    }
}