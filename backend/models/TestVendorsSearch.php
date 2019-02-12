<?php

namespace backend\models;

use common\models\TestVendors;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrganizationSearch represents the model behind the search form about `common\models\Organization`.
 *
 * @property int    $vendor_id  [int(11)]  Идентификатор организации-поставщика
 * @property string $guide_name [varchar(255)]  Наименование шаблона закупок
 * @property bool   $is_active  [tinyint(1)]  Показатель состояния активности (0 - не активно, 1 - активно)
 */
class TestVendorsSearch extends TestVendors
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor_id'], 'integer'],
            [['guide_name'], 'string'],
            [['is_active'], 'boolean'],
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
        $query = TestVendors::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['pageSize' => 20]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'vendor_id', $this->vendor_id])
            ->andFilterWhere(['like', 'guide_name', $this->guide_name])
            ->andFilterWhere(['like', 'is_active', $this->is_active]);

        return $dataProvider;
    }

}
