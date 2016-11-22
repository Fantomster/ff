<?php

namespace common\models\search;

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

    public $vendor_search_id = null;
    public $client_search_id = null;
    private $status_array;
    public $date_from;
    public $date_to;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['client_id', 'vendor_id', 'created_by_id', 'accepted_by_id', 'status', 'total_price', 'client_search_id', 'vendor_search_id'], 'integer'],
            [['created_at', 'updated_at', 'date_from', 'date_to'], 'safe'],
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
        return array_merge(parent::attributes(), ['acceptedByProfile.full_name', 'vendor.name', 'client.name', 'createdByProfile.full_name']);
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
        $this->load($params);

        $filter_date_from = strtotime($this->date_from);
        $filter_date_to = strtotime($this->date_to);

//        $from = \DateTime::createFromFormat('d.m.Y', $this->date_from);
        $from = \DateTime::createFromFormat('d.m.Y', $this->date_from);
        if ($from) {
            $t1_f = $from->format('Y-m-d');
        }
        $to = \DateTime::createFromFormat('d.m.Y', $this->date_to);
        if ($to) {
            $to->add(new \DateInterval('P1D'));
            $t2_f = $to->format('Y-m-d');
        }

        switch ($this->status) {
            case 1: //new
                $this->status_array = [Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT];
                break;
            case 2: //canceled
                $this->status_array = [Order::STATUS_REJECTED, Order::STATUS_CANCELLED];
                break;
            case 3: //processing
                $this->status_array = [Order::STATUS_PROCESSING];
                break;
            case 4: //done
                $this->status_array = [Order::STATUS_DONE];
                break;
        }

        if (!$this->vendor_search_id) {
            $query->joinWith([
                'vendor' => function ($query) {
                    $query->from(Organization::tableName() . ' vendor');
                },
            ]);
        } else {
            $query->joinWith([
                'client' => function ($query) {
                    $query->from(Organization::tableName() . ' client');
                },
            ]);
        }
        $query->joinWith([
            'createdByProfile' => function($query) {
                $query->from(Profile::tableName(). ' createdByProfile');
            },
                ], true);
        $query->joinWith([
            'acceptedByProfile' => function($query) {
                $query->from(Profile::tableName(). ' acceptedByProfile');
            },
                ], true);
        $query->where(Order::tableName() . '.status!= :status', ['status' => Order::STATUS_FORMING]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $addSortAttributes = $this->vendor_search_id ? ['client.name'] : ['vendor.name'];
        $addSortAttributes[] = 'createdByProfile.full_name';
        $addSortAttributes[] = 'acceptedByProfile.full_name';
        foreach ($addSortAttributes as $addSortAttribute) {
            $dataProvider->sort->attributes[$addSortAttribute] = [
                'asc' => [$addSortAttribute => SORT_ASC],
                'desc' => [$addSortAttribute => SORT_DESC],
            ];
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            Order::tableName() . '.status' => $this->status_array,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if (isset($t1_f)) {
            $query->andFilterWhere(['>=', Order::tableName() . '.created_at', $t1_f]);
        }
        if (isset($t2_f)) {
            $query->andFilterWhere(['<=', Order::tableName() . '.created_at', $t2_f]);
        }

        if ($this->vendor_id) {
            $query->andFilterWhere(['vendor_id' => $this->vendor_id]);
        }

        if ($this->client_id) {
            $query->andFilterWhere(['client_id' => $this->client_id]);
        }

        return $dataProvider;
    }

}
