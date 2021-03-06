<?php

namespace franchise\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use common\models\Order;
use yii\db\Query;
use yii\db\Expression;

/**
 * Description of AgentOrganizationSearch
 *
 * @author sharaf
 */
class AgentOrganizationSearch extends Organization
{

    public $searchString;
    public $date_from;
    public $date_to;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type_id'], 'integer'],
            [['name', 'vendorCount', 'orderCount', 'orderSum', 'created_at', 'contact_name', 'phone', 'date_from', 'date_to', 'searchString'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params, $userId)
    {
        $this->load($params);

        //$searchString = "%$this->searchString%";

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
                ->where(["rest_org_id = org.id"]);

        $subQueryVendorCountPrev30 = (new Query())
                ->select([new Expression("COUNT(id)")])
                ->from($tblRSR)
                ->where(["deleted"     => 0])
                ->andWhere("rest_org_id = org.id")
                ->andWhere([
            "between",
            "created_at",
            new Expression("CURDATE() - INTERVAL 30 DAY"),
            new Expression("CURDATE() + INTERVAL 1 DAY"),
        ]);

        $subQueryOrderCount = (new Query())
                ->select([new Expression("COUNT(id)")])
                ->from($tblOrder)
                ->where(["status"    => $orderStatuses])
                ->andWhere("client_id = org.id");

        $subQueryOrderCountPrev30 = (new Query())
                ->select([new Expression("COUNT(id)")])
                ->from($tblOrder)
                ->where(["status"    => $orderStatuses])
                ->andWhere("client_id = org.id")
                ->andWhere([
            "between",
            "created_at",
            new Expression("CURDATE() - INTERVAL 30 DAY"),
            new Expression("CURDATE() + INTERVAL 1 DAY"),
        ]);

        $subQueryOrderSum = (new Query())
                ->select([new Expression("SUM(total_price)")])
                ->from($tblOrder)
                ->where(["status"    => $orderStatuses])
                ->andWhere("client_id = org.id");

        $subQueryOrderSumPrev30 = (new Query())
                ->select([new Expression("SUM(total_price)")])
                ->from($tblOrder)
                ->where(["status"    => $orderStatuses])
                ->andWhere("client_id = org.id")
                ->andWhere([
            "between",
            "created_at",
            new Expression("CURDATE() - INTERVAL 30 DAY"),
            new Expression("CURDATE() + INTERVAL 1 DAY"),
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
                    ["fa.agent_id" => $userId],
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
