<?php

namespace backend\models;

use common\models\TestVendors;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrganizationSearch represents the model behind the search form about `common\models\Organization`.
 */
class TestVendorsSearch extends TestVendors {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['vendor_id'], 'integer'],
            [['guide_name'], 'string'],
            [['is_active'], 'boolean'],
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
        $query = TestVendors::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['pageSize' => 20]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'guide_name' => $this->guide_name,
            'is_active' => $this->is_active,
        ]);
        return $dataProvider;
    }

}
