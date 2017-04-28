<?php

namespace franchise\models;

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

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'type_id'], 'integer'],
            [['name', 'vendorCount', 'orderCount', 'orderSum', 'created_at', 'contact_name', 'phone', 'date_from', 'date_to', 'search_string'], 'safe'],
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
    public function search($params, $franchisee_id) {
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

        $query = "SELECT org.id as id, org.name as name, (select count(id) from relation_supp_rest where rest_org_id=org.id) as vendorCount, 
                (select count(id) from relation_supp_rest where rest_org_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY and status in (1,2,3,4)) as vendorCount_prev30, 
                (select count(id) from `order` where client_id=org.id and status in (1,2,3,4)) as orderCount,
                (select count(id) from `order` where client_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY and status in (1,2,3,4)) as orderCount_prev30,
                (select sum(total_price) from `order` where client_id=org.id and status in (1,2,3,4)) as orderSum,
                (select sum(total_price) from `order` where client_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY and status in (1,2,3,4)) as orderSum_prev30,
                org.created_at as created_at, org.contact_name as contact_name, org.phone as phone
                FROM `organization` AS org
                LEFT JOIN  `franchisee_associate` AS fa ON org.id = fa.organization_id
                WHERE fa.franchisee_id = $franchisee_id and org.type_id=1 and org.created_at between :dateFrom and :dateTo
                and (org.name like :searchString or org.contact_name like :searchString or org.phone like :searchString)";

        $count = count(Yii::$app->db->createCommand($query, [':searchString' => $searchString, ':dateFrom' => $t1_f, 'dateTo' => $t2_f])->queryAll());

        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query,
            'params' => [':searchString' => $searchString, ':dateFrom' => $t1_f, 'dateTo' => $t2_f],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'name',
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
