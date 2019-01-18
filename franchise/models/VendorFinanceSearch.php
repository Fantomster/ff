<?php

namespace franchise\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Order;
use common\models\Organization;

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
        $ordTable = Order::tableName();
        $orgTable = Organization::tableName();
        $frTable = \common\models\Franchisee::tableName();
        $ftypeTable = \common\models\FranchiseType::tableName();
        
        $orderStatuses = "(".Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR.",".Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT.",".Order::STATUS_PROCESSING.",".Order::STATUS_DONE.")";
        $query = "SELECT $orgTable.id as id, $orgTable.name as name, TRUNCATE(SUM($ordTable.total_price * $biTable.reward / 100),2) as turnoverCut, (0) as fromFkeeper, TRUNCATE(SUM($ordTable.total_price * $biTable.reward / 100) * (100 - $ftypeTable.share) / 100,2) as toFkeeper "
            . "FROM $ordTable "
            . "LEFT JOIN $faTable ON $ordTable.vendor_id = $faTable.organization_id "
            . "LEFT JOIN $rsrTable ON $ordTable.vendor_id = $rsrTable.supp_org_id "
            . "LEFT JOIN $biTable ON $ordTable.vendor_id = $biTable.organization_id "
                    . "LEFT JOIN $frTable ON $faTable.franchisee_id = $frTable.id "
                    . "LEFT JOIN $ftypeTable ON $frTable.type_id = $ftypeTable.id "
                    . "LEFT JOIN $orgTable ON $faTable.organization_id = $orgTable.id "
            . "WHERE $faTable.franchisee_id = :franchisee_id AND $ordTable.status IN $orderStatuses "
            . "GROUP BY $ordTable.vendor_id";
        
//':searchString' => $searchString, ':month' => $this->month, 
        $count = count(Yii::$app->db->createCommand($query, [':franchisee_id' =>$franchisee_id])->queryAll());

        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query,
            'params' => [':franchisee_id' =>$franchisee_id],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'name',
                ],
            ],
        ]);

        return $dataProvider;
    }

}
