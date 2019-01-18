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
     *
     * @return ActiveDataProvider
     */
    public function search($params, $franchisee_id, $vendor_id = null) {
        $this->load($params);

        $searchString = "%$this->searchString%";
        $filter_date_from = strtotime($this->date_from);
        $filter_date_to = strtotime($this->date_to);

        $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
        if ($from) {
            $t1_f = $from->format('Y-m-d');
        }
        $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
        if ($to) {
            $to->add(new \DateInterval('P1D'));
            $t2_f = $to->format('Y-m-d');
        }
        $currencyOption = '';
        if($this->filter_currency!=null){
            $currencyOption = " and currency_id=$this->filter_currency";
        }

        $query = "SELECT fa.id as franchisee_associate_id, self_registered, org.id as id, org.name as name, (select count(id) from relation_supp_rest where rest_org_id=org.id) as vendorCount, 
                (select count(id) from relation_supp_rest where rest_org_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY and status in (1,2,3,4)) as vendorCount_prev30, 
                (select count(id) from " . Order::tableName() . " where client_id=org.id and status in (1,2,3,4)) as orderCount,
                (select count(id) from " . Order::tableName() . " where client_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY and status in (1,2,3,4)) as orderCount_prev30,
                (select sum(total_price) from " . Order::tableName() . " where client_id=org.id $currencyOption and status in (1,2,3,4)) as orderSum,
                (select sum(total_price) from " . Order::tableName() . " where client_id=org.id $currencyOption and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY and status in (1,2,3,4)) as orderSum_prev30,
                org.created_at as created_at, org.contact_name as contact_name, org.phone as phone
                FROM organization AS org
                LEFT JOIN  franchisee_associate AS fa ON org.id = fa.organization_id
                WHERE fa.franchisee_id = $franchisee_id and org.type_id=1 and org.created_at between :dateFrom and :dateTo
                and (org.name like :searchString or org.contact_name like :searchString or org.phone like :searchString)";

        if($vendor_id){
            $query = parent::getOrganizationQuery($vendor_id, 'rest', $this->filter_currency ?? 1);
        }

        if(Yii::$app->user->identity->role_id == Role::ROLE_FRANCHISEE_LEADER){
            $query.=" and (org.manager_id=".Yii::$app->user->id." or org.manager_id in(select manager_id from relation_manager_leader where leader_id=".Yii::$app->user->id."))";
        }

        if(Yii::$app->user->identity->role_id == Role::ROLE_FRANCHISEE_MANAGER){
            $query.=" and org.manager_id=".Yii::$app->user->id;
        }

        $count = count(Yii::$app->db->createCommand($query, [':searchString' => $searchString, ':dateFrom' => $t1_f, 'dateTo' => $t2_f])->queryAll());

        $dataProvider = new \yii\data\SqlDataProvider([
            'sql'        => $query,
            'params'     => [':searchString' => $searchString, ':dateFrom' => $t1_f, 'dateTo' => $t2_f],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort'       => [
                'attributes'   => [
                    'name',
                    'self_registered',
                    'vendorCount',
                    'orderCount',
                    'orderSum',
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
