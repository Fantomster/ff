<?php

namespace franchise\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;

/**
 * Description of VendorSearch
 *
 * @author sharaf
 */
class VendorSearch extends Organization {

    public $searchString;
    public $date_from;
    public $date_to;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'type_id'], 'integer'],
            [['name', 'clientCount', 'orderCount', 'orderSum', 'created_at', 'contact_name', 'phone', 'date_from', 'date_to', 'searchString'], 'safe'],
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
     * @return SqlDataProvider
     */
    public function search($params, $franchisee_id) {
        $this->load($params);

        $searchString = "%{$this->searchString}%";
        $filter_date_from = strtotime($this->date_from);
        $filter_date_to = strtotime($this->date_to);

        $from = \DateTime::createFromFormat('d.m.Y', $this->date_from);
        if ($from) {
            $t1_f = $from->format('Y-m-d');
        }
        $to = \DateTime::createFromFormat('d.m.Y', $this->date_to);
        if ($to) {
            $to->add(new \DateInterval('P1D'));
            $t2_f = $to->format('Y-m-d');
        }

        $query = "SELECT org.id as id, org.name as name, (select count(id) from relation_supp_rest where supp_org_id=org.id) as clientCount, 
                (select count(id) from relation_supp_rest where supp_org_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()) as clientCount_prev30, 
                count(ord.id) as orderCount,
                (select count(id) from `order` where vendor_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() ) as orderCount_prev30,
                sum(ord.total_price) as orderSum,
                (select sum(total_price) from `order` where vendor_id=org.id and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() ) as orderSum_prev30,
                org.created_at as created_at, org.contact_name as contact_name, org.phone as phone
                FROM `organization` AS org
                LEFT JOIN  `franchisee_associate` AS fa ON org.id = fa.organization_id
                left join `order` as ord on org.id=ord.vendor_id
                WHERE fa.franchisee_id = $franchisee_id and org.type_id=2 and ord.status in (1,2,3,4) and org.created_at between :dateFrom and :dateTo
                and (org.name like :searchString or org.contact_name like :searchString or org.phone like :searchString)
                GROUP by ord.vendor_id";

        $count = Yii::$app->db->createCommand($query, [':searchString' => $searchString, ':dateFrom' => $t1_f, 'dateTo' => $t2_f])->queryScalar();

        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query,
            'params' => [':searchString' => $searchString, ':dateFrom' => $t1_f, 'dateTo' => $t2_f],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
                'page' => isset($params['page']) ? ($params['page'] - 1) : 0,
            ],
            'sort' => [
                'attributes' => [
                    'name',
                    'clientCount',
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
