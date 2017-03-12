<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Order;
use common\models\Organization;
use common\models\Profile;

/**
 * OrderSearch represents the model behind the search form about `common\models\Order`.
 */
class OrderSearch extends Order {

    public $client_name;
    public $vendor_name;
    public $client_manager;
    public $vendor_manager;
    private $blacklist = '(1,2,5,16,63,88,99,100,106,108,111,114,116,272,284,333,440,449,526,673,784,824,1037)'; 

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'client_id', 'vendor_id', 'created_by_id', 'accepted_by_id', 'status', 'discount_type'], 'integer'],
            [['total_price', 'discount'], 'number'],
            [['created_at', 'updated_at', 'requested_delivery', 'actual_delivery', 'comment', 'client_name', 'vendor_name', 'client_manager', 'vendor_manager'], 'safe'],
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
        $profileTable = Profile::tableName();
        $organizationTable = Organization::tableName();
        $orderTable = Order::tableName();

        $query = Order::find();

        $query->joinWith([
            'vendor' => function ($query) use ($organizationTable) {
                $query->from($organizationTable . ' vendor');
            },
        ]);

        $query->joinWith([
            'client' => function ($query) use ($organizationTable) {
                $query->from($organizationTable . ' client');
            },
        ]);

        $query->joinWith([
            'createdByProfile' => function($query) use ($profileTable) {
                $query->from($profileTable . ' createdByProfile');
            },
                ], true);
        $query->joinWith([
            'acceptedByProfile' => function($query) use ($profileTable) {
                $query->from($profileTable . ' acceptedByProfile');
            },
                ], true);

        // add conditions that should always apply here
        $query->where("`$orderTable`.client_id not in $this->blacklist");
        $query->andWhere("`$orderTable`.status <> " . Order::STATUS_FORMING);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $dataProvider->sort->attributes['client_name'] = [
            'asc' => ["client.name" => SORT_ASC],
            'desc' => ["client.name" => SORT_DESC],
        ];

        $dataProvider->sort->attributes['vendor_name'] = [
            'asc' => ["vendor.name" => SORT_ASC],
            'desc' => ["vendor.name" => SORT_DESC],
        ];

        $dataProvider->sort->attributes['client_manager'] = [
            'asc' => ["createdByProfile.full_name" => SORT_ASC],
            'desc' => ["createdByProfile.full_name" => SORT_DESC],
        ];

        $dataProvider->sort->attributes['vendor_manager'] = [
            'asc' => ["acceptedByProfile.full_name" => SORT_ASC],
            'desc' => ["acceptedByProfile.full_name" => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'client_id' => $this->client_id,
            'vendor_id' => $this->vendor_id,
            'created_by_id' => $this->created_by_id,
            'accepted_by_id' => $this->accepted_by_id,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'requested_delivery' => $this->requested_delivery,
            'actual_delivery' => $this->actual_delivery,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
        ]);

        $query
                ->andFilterWhere(['like', 'comment', $this->comment])
                ->andFilterWhere(['like', "client.name", $this->client_name])
                ->andFilterWhere(['like', "vendor.name", $this->vendor_name])
                ->andFilterWhere(['like', "createdByProfile.full_name", $this->client_manager])
                ->andFilterWhere(['like', "acceptedByProfile.full_name", $this->vendor_manager])
        ;

        return $dataProvider;
    }

}
