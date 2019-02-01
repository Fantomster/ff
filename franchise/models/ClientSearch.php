<?php

namespace franchise\models;

use common\models\Order;
use common\models\Role;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;

/**
 * Description of ClientSearch
 *
 * @author sharaf
 */
class ClientSearch extends Organization {

    public $searchString;
    public $date_from;
    public $date_to;
    public $filter_currency;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'type_id'], 'integer'],
            [['name', 'vendorCount', 'orderCount', 'orderSum', 'created_at', 'contact_name', 'phone', 'date_from', 'date_to', 'searchString'], 'safe'],
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
     * @param int $franchiseeId
     * @param User $user
     *
     * @return ActiveDataProvider
     */
    public function search($params, $franchiseeId, $user) {
        $this->load($params);

        $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
        if ($from) {
            $t1_f = $from->format('Y-m-d');
        }
        $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
        if ($to) {
            $to->add(new \DateInterval('P1D'));
            $t2_f = $to->format('Y-m-d');
        }

        $tblRSR   = \common\models\RelationSuppRest::tableName();
        $tblOrder = Order::tableName();
        $tblOrg   = Organization::tableName();
        $tblFA    = \common\models\FranchiseeAssociate::tableName();

        $orderStatuses = [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            Order::STATUS_PROCESSING,
            Order::STATUS_DONE,
        ];

        $subQueryVendorCount = (new Query())
                ->select([new Expression("COUNT(id)")])
                ->from($tblRSR)
                ->where(["rest_org_id" => "org.id"]);

        $subQueryVendorCountPrev30 = (new Query())
                ->select([new Expression("COUNT(id)")])
                ->from($tblRSR)
                ->where([
                    "rest_org_id" => "org.id",
                    "deleted"     => 0,
                ])
                ->andWhere([
                    "between",
                    "created_at",
                    new Expression("CURDATE() - INTERVAL 30 DAY"),
                    new Expression("CURDATE() + INTERVAL 1 DAY"),
                ]);

        $subQueryOrderCount = (new Query())
                ->select([new Expression("COUNT(id)")])
                ->from($tblOrder)
                ->where([
                    "client_id" => "org.id",
                    "status"    => $orderStatuses,
                ]);

        $subQueryOrderCountPrev30 = (new Query())
                ->select([new Expression("COUNT(id)")])
                ->from($tblOrder)
                ->where([
                    "client_id" => "org.id",
                    "status"    => $orderStatuses,
                ])
                ->andWhere([
                    "between",
                    "created_at",
                    new Expression("CURDATE() - INTERVAL 30 DAY"),
                    new Expression("CURDATE() + INTERVAL 1 DAY"),
                ]);

        $subQueryOrderSum = (new Query())
                ->select([new Expression("SUM(total_price)")])
                ->from($tblOrder)
                ->where([
                    "client_id" => "org.id",
                    "status"    => $orderStatuses,
                ])->andFilterWhere([
                    "currency_id" => $this->filter_currency
                ]);

        $subQueryOrderSumPrev30 = (new Query())
                ->select([new Expression("SUM(total_price)")])
                ->from($tblOrder)
                ->where([
                    "client_id" => "org.id",
                    "status"    => $orderStatuses,
                ])
                ->andWhere([
                    "between",
                    "created_at",
                    new Expression("CURDATE() - INTERVAL 30 DAY"),
                    new Expression("CURDATE() + INTERVAL 1 DAY"),
                ])->andFilterWhere([
                    "currency_id" => $this->filter_currency
                ]);

        $query = (new Query())
                ->select([
                    "franchisee_associate_id" => "fa.id",
                    "self_registered"         => "self_registered",
                    "id"                      => "org.id",
                    "name"                    => "org.name",
                    "vendor_count"            => $subQueryVendorCount,
                    "vendor_count_prev30"     => $subQueryVendorCountPrev30,
                    "order_count"             => $subQueryOrderCount,
                    "order_count_prev30"      => $subQueryOrderCountPrev30,
                    "order_sum"               => $subQueryOrderSum,
                    "order_sum_prev30"        => $subQueryOrderSumPrev30,
                    "created_at"              => "org.created_at",
                    "contact_name"            => "org.contact_name",
                    "phone"                   => "org.phone",
                ])
                ->from(["org" => $tblOrg])
                ->leftJoin(['fa' => $tblFA], "org.id = fa.organization_id")
                ->where([
                    "and",
                    ["fa.franchisee_id" => $franchiseeId],
                    ["org.type_id" => Organization::TYPE_RESTAURANT],
                    [
                        "between",
                        "org.created_at",
                        $t1_f,
                        $t2_f,
                    ]
                ])
                ->andFilterWhere([
            "or",
            ["like", "org.name", $this->searchString],
            ["like", "org.contact_name", $this->searchString],
            ["like", "org.phone", $this->searchString],
        ]);

        if ($user->role_id == Role::ROLE_FRANCHISEE_LEADER) {
            $subQueryManagerIds = (new Query())
                ->select(["manager_id"])
                ->from(\common\models\RelationManagerLeader::tableName())
                ->where(["leader_id" => $user->id]);
            $query->andWhere([
                "or",
                ["org.manager_id" => $user->id],
                ["org.manager_id" => $subQueryManagerIds],
            ]);
        }
        
        if ($user->role_id == Role::ROLE_FRANCHISEE_MANAGER){
            $query->andWhere(["org.manager_id" => $user->id]);
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort'       => [
                'attributes'   => [
                    'name',
                    'self_registered',
                    'vendor_count',
                    'order_count',
                    'order_sum',
                    'created_at',
                    'contact_name',
                    'phone'
                ],
                'defaultOrder' => [
                    'created_at' => SORT_ASC
                ]
            ],
        ]);
        
        return $dataProvider;
    }

}
