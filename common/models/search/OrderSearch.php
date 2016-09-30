<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Order;
use common\models\User;
use common\models\Organization;

/**
 * OrderSearch represents the model behind the search form about `common\models\Order`.
 */
class OrderSearch extends Order {
//    public $client_id = null;
//    public $vendor_id = null;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['client_id', 'vendor_id', 'created_by_id', 'accepted_by_id', 'status', 'total_price'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

//     * @property User $acceptedBy
// * @property Organization $client
// * @property User $createdBy
// * @property Organization $vendor
// * @property OrderContent[] $orderContent
// * @property OrderChat[] $orderChat

    /**
     * @inheritdoc
     */
    public function attributes() {
        return array_merge(parent::attributes(), ['acceptedBy.profile.full_name', 'vendor.name', 'createdBy.profile.full_name']);
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
        $query = Order::find();

        $query->joinWith([
            'vendor' => function ($query) {
                $query->from(Organization::tableName() . ' vendor');
            },
        ]);
        $query->joinWith([
            'acceptedBy' => function ($query) {
                $query->from(User::tableName() . ' acceptedBy');
            },
        ], true);
        $query->joinWith([
            'createdBy' => function ($query) {
                $query->from(User::tableName() . ' createdBy');
            },
        ], true);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $addSortAttributes = ['vendor.name'];
        foreach ($addSortAttributes as $addSortAttribute) {
            $dataProvider->sort->attributes[$addSortAttribute] = [
                'asc' => [$addSortAttribute => SORT_ASC],
                'desc' => [$addSortAttribute => SORT_DESC],
            ];
        }

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'status' => $this->status,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }

}
