<?php

namespace franchise\models;

use common\models\Order;
use common\models\Role;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use common\models\User;
use yii\db\Query;
use yii\db\Expression;

/**
 * Description of ClientSearch
 *
 * @author sharaf
 */
class AssociatedOrganizationsSearch extends Organization{

    public $filter_currency = 1;
    public $searchString;
    public $date_from;
    public $date_to;

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param Organization $organization
     * @param User $user
     *
     * @return ActiveDataProvider
     */
    public function search($params, $organization, $user) {
        $this->load($params);

        $tblRSR   = \common\models\RelationSuppRest::tableName();
        $tblOrder = Order::tableName();
        $tblOrg   = Organization::tableName();
        $tblFA    = \common\models\FranchiseeAssociate::tableName();

        $prefix = ($organization->type_id == Organization::TYPE_RESTAURANT) ? 'supp' : 'rest';
        $name = ($organization->type_id == Organization::TYPE_RESTAURANT) ? 'client' : 'vendor';
        
        $orderStatuses = [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            Order::STATUS_PROCESSING,
            Order::STATUS_DONE,
        ];

        $subQueryAssociatedCount = (new Query())
                ->select([new Expression("COUNT(id)")])
                ->from($tblRSR)
                ->where(["{$prefix}_org_id" => "org.id"]);

        $subQueryAssociatedCountPrev30 = (new Query())
                ->select([new Expression("COUNT(id)")])
                ->from($tblRSR)
                ->where([
                    "{$prefix}_org_id" => "org.id",
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
                    "{$name}_id" => "org.id",
                    "status"    => $orderStatuses,
                ]);

        $subQueryOrderCountPrev30 = (new Query())
                ->select([new Expression("COUNT(id)")])
                ->from($tblOrder)
                ->where([
                    "{$name}_id" => "org.id",
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
                    "{$name}_id" => "org.id",
                    "status"    => $orderStatuses,
                ])->andFilterWhere([
                    "currency_id" => $this->filter_currency
                ]);

        $subQueryOrderSumPrev30 = (new Query())
                ->select([new Expression("SUM(total_price)")])
                ->from($tblOrder)
                ->where([
                    "{$name}_id" => "org.id",
                    "status"    => $orderStatuses,
                ])
                ->andWhere([
                    "between",
                    "created_at",
                    new Expression("CURDATE() - INTERVAL 30 DAY"),
                    new Expression("CURDATE() + INTERVAL 1 DAY"),
                ])->andWhere([
                    "currency_id" => $this->filter_currency
                ]);

        $query = (new Query())
                ->select([
                    "franchisee_associate_id" => "fa.id",
                    "self_registered"         => "self_registered",
                    "id"                      => "org.id",
                    "name"                    => "org.name",
                    "associated_count"        => $subQueryAssociatedCount,
                    "associated_count_prev30" => $subQueryAssociatedCountPrev30,
                    "order_count"             => $subQueryOrderCount,
                    "order_count_prev30"      => $subQueryOrderCountPrev30,
                    "order_sum"               => $subQueryOrderSum,
                    "order_sum_prev30"        => $subQueryOrderSumPrev30,
                    "created_at"              => "org.created_at",
                    "contact_name"            => "org.contact_name",
                    "phone"                   => "org.phone",
                ])
                ->from(["rel" => $tblRSR])
                ->leftJoin(["org" => $tblOrg], "org.id = rel.{$prefix}_org_id")
                ->leftJoin(['fa' => $tblFA], "org.id = fa.organization_id")
                ->where([
                    "and",
                    ["rel.{$prefix}_org_id" => $organization->id],
                    ["org.type_id" => $organization->type_id],
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
                    'associated_count',
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
