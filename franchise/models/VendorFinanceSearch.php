<?php

namespace franchise\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Description of VendorSearch
 *
 * @author sharaf
 */
class VendorFinanceSearch extends Organization {

    public $searchString;
    public $month;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id'], 'integer'],
            [['name', 'turnoverCut', 'fromFkeeper', 'toFkeeper', 'totalShare', 'month', 'searchString'], 'safe'],
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

        $faTable = \common\models\FranchiseeAssociate::tableName();
        $rsrTable = \common\models\RelationSuppRest::tableName();
        $biTable = \common\models\BuisinessInfo::tableName();
        $ordTable = \common\models\Order::tableName();
        $orgTable = \common\models\Organization::tableName();
        
        $query = "SELECT $orgTable.name as name, SUM($ord.total_price * $rsrTable.reward / 100) as turnoverCut, (0) as fromFkeeper, SUM($ord)";
        

        $count = count(Yii::$app->db->createCommand($query, [':searchString' => $searchString, ':month' => $this->month])->queryAll());

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
