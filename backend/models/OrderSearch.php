<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Order;
use common\models\OrderStatus;
use common\models\Organization;
use common\models\Profile;
use yii\db\Query;

/**
 * OrderSearch represents the model behind the search form about `common\models\Order`.
 */
class OrderSearch extends Order
{

    public $client_name;
    public $vendor_name;
    public $client_manager;
    public $vendor_manager;
    public $client_city;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id', 'client_id', 'vendor_id', 'created_by_id', 'accepted_by_id', 'status', 'discount_type'], 'integer'],
            [['total_price', 'discount'], 'number'],
            [['created_at', 'updated_at', 'requested_delivery', 'actual_delivery', 'comment', 'client_name', 'client_city', 'vendor_name', 'client_manager', 'vendor_manager'], 'safe'],
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
        $profileTable      = Profile::tableName();
        $organizationTable = Organization::tableName();
        $orderTable        = Order::tableName();

        $query = Order::find();

        $query->joinWith([
            'vendor' => function (Query $query) use ($organizationTable) {
                $query->from(["vendor" => $organizationTable]);
            },
        ])->joinWith([
            'client' => function (Query $query) use ($organizationTable) {
                $query->from(["client" => $organizationTable]);
            },
        ])->joinWith([
            'createdByProfile' => function (Query $query) use ($profileTable) {
                $query->from(["createdByProfile" => $profileTable]);
            },
                ], true)->joinWith([
            'acceptedByProfile' => function (Query $query) use ($profileTable) {
                $query->from(["acceptedByProfile" => $profileTable]);
            },
                ], true);

        // add conditions that should always apply here
        $query->where("vendor.blacklisted in (0, 1) ")
                ->andWhere("$orderTable.status <> " . OrderStatus::STATUS_FORMING);


        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
            'pagination' => ['pageSize' => 20]
        ]);

        $dataProvider->sort->attributes['client_name'] = [
            'asc'  => ["client.name" => SORT_ASC],
            'desc' => ["client.name" => SORT_DESC],
        ];

        $dataProvider->sort->attributes['vendor_name'] = [
            'asc'  => ["vendor.name" => SORT_ASC],
            'desc' => ["vendor.name" => SORT_DESC],
        ];

        $dataProvider->sort->attributes['client_manager'] = [
            'asc'  => ["createdByProfile.full_name" => SORT_ASC],
            'desc' => ["createdByProfile.full_name" => SORT_DESC],
        ];

        $dataProvider->sort->attributes['vendor_manager'] = [
            'asc'  => ["acceptedByProfile.full_name" => SORT_ASC],
            'desc' => ["acceptedByProfile.full_name" => SORT_DESC],
        ];

        $dataProvider->sort->attributes['client_city'] = [
            'asc'  => ["client.locality" => SORT_ASC],
            'desc' => ["client.locality" => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'order.id'           => $this->id,
            'client_id'          => $this->client_id,
            'vendor_id'          => $this->vendor_id,
            'created_by_id'      => $this->created_by_id,
            'accepted_by_id'     => $this->accepted_by_id,
            'status'             => $this->status,
            'total_price'        => $this->total_price,
            'order.created_at'   => $this->created_at,
            'order.updated_at'   => $this->updated_at,
            'requested_delivery' => $this->requested_delivery,
            'actual_delivery'    => $this->actual_delivery,
            'discount'           => $this->discount,
            'discount_type'      => $this->discount_type,
        ]);

        $query
                ->andFilterWhere(['like', 'comment', $this->comment])
                ->andFilterWhere(['like', "client.name", $this->client_name])
                ->andFilterWhere(['like', "client.locality", $this->client_city])
                ->andFilterWhere(['like', "vendor.name", $this->vendor_name])
                ->andFilterWhere(['like', "createdByProfile.full_name", $this->client_manager])
                ->andFilterWhere(['like', "acceptedByProfile.full_name", $this->vendor_manager]);

        return $dataProvider;
    }

}
