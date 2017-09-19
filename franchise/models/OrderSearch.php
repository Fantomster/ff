<?php

namespace franchise\models;

use common\models\RelationManagerLeader;
use common\models\Role;
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

    public $searchString;
    public $date_from;
    public $date_to;
    private $status_array;
    public $clientName;
    public $clientManager;
    public $vendorName;
    public $vendorManager;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['client_id', 'vendor_id', 'created_by_id', 'accepted_by_id', 'status', 'total_price'], 'integer'],
            [['created_at', 'updated_at', 'date_from', 'date_to', 'searchString', 'clientName', 'clientManager', 'vendor', 'vendorManager'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return "{{%order}}";
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $franchisee_id, $prev30 = false) {
        $orderTable = Order::tableName();
        $query = Order::find();
        $this->load($params);
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

        $query->joinWith([
            'vendor' => function ($query) {
                $query->from(Organization::tableName() . ' vendor');
            },
        ], true);
        $query->joinWith([
            'client' => function ($query) {
                $query->from(Organization::tableName() . ' client');
            },
        ], true);
        $query->joinWith([
            'createdByProfile' => function($query) {
                $query->from(Profile::tableName() . ' createdByProfile');
            },
                ], true);
        $query->joinWith([
            'acceptedByProfile' => function($query) {
                $query->from(Profile::tableName() . ' acceptedByProfile');
            },
                ], true);
        $query->leftJoin("franchisee_associate", "franchisee_associate.organization_id = client.id");
        $query->where(Order::tableName() . '.status != ' .Order::STATUS_FORMING);
        $query->andWhere(['franchisee_associate.franchisee_id' => $franchisee_id]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $dataProvider->sort->attributes['id'] = [
            'asc' => ["$orderTable.id" => SORT_ASC],
            'desc' => ["$orderTable.id" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['vendorName'] = [
            'asc' => ["vendor.name" => SORT_ASC],
            'desc' => ["vendor.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['clientName'] = [
            'asc' => ["client.name" => SORT_ASC],
            'desc' => ["client.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['clientManager'] = [
            'asc' => ["createdByProfile.full_name" => SORT_ASC],
            'desc' => ["createdByProfile.full_name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['vendorManager'] = [
            'asc' => ["acceptedByProfile.full_name" => SORT_ASC],
            'desc' => ["acceptedByProfile.full_name" => SORT_DESC],
        ];

        // grid filtering conditions
        $query->andFilterWhere(['or',
            ['like', 'client.name', $this->searchString],
            ['like', 'vendor.name', $this->searchString],
            ['like', 'createdByProfile.full_name', $this->searchString],
            ['like', 'acceptedByProfile.full_name', $this->searchString],
            ]);

        if(Yii::$app->user->identity->role_id == Role::ROLE_FRANCHISEE_MANAGER or Yii::$app->user->identity->role_id == Role::ROLE_FRANCHISEE_LEADER){
            $searchArr[] = Yii::$app->user->id;
            if(Yii::$app->user->identity->role_id == Role::ROLE_FRANCHISEE_LEADER){
                $relArr = RelationManagerLeader::findAll(['leader_id'=>Yii::$app->user->id]);
                foreach ($relArr as $one){
                    $searchArr[] = $one['manager_id'];
                }
            }
            $query->andFilterWhere(['or',
                ['client.manager_id'=>$searchArr],
                ['vendor.manager_id'=>$searchArr],
            ]);
        }

        $query->andFilterWhere([
            Order::tableName() . '.status' => $this->status_array,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        if ($prev30) {
            $query->andWhere("$orderTable.created_at between CURDATE() - INTERVAL 30 DAY and CURDATE() + INTERVAL 1 DAY ");
        } else {
            $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
            $t1_f = null;
            $t2_f = null;
            if ($from) {
                $t1_f = $from->format('Y-m-d');
            }
            $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
            if ($to) {
                $to->add(new \DateInterval('P1D'));
                $t2_f = $to->format('Y-m-d');
            }
            $query->andFilterWhere(['>=', "$orderTable.created_at", $t1_f]);
            $query->andFilterWhere(['<=', "$orderTable.created_at", $t2_f]);
        }

        return $dataProvider;
    }

}
