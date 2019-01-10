<?php

namespace common\models\search;

use common\models\RelationSuppRest;
use common\models\ManagerAssociate;
use common\models\Organization;
use common\models\Order;
use common\models\Catalog;
use yii\data\ActiveDataProvider;

/**
 *
 * @author sharaf
 */
class ClientSearch extends RelationSuppRest {
    
    public $client_name;
    public $catalog_name;
    public $manager_name;
    public $last_order_date;
    public $search_string;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cat_id', 'rest_org_id', 'invite'], 'integer'],
            [['client_name', 'catalog_name', 'manager_name', 'last_order_date', 'search_string'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), ['client_name', 'catalog_name', 'manager_name', 'last_order_date', 'search_string']);
    }
    
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return RelationSuppRest::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $vendor_id, $manager_id = null)
    {
        $rspTable = RelationSuppRest::tableName();
        $orgTable = Organization::tableName();
        $orderTable = Order::tableName();
        $catTable = Catalog::tableName();
        $maTable = ManagerAssociate::tableName();

        $query = RelationSuppRest::find()
                ->select("$rspTable.*, $orgTable.name as client_name, $catTable.name as catalog_name, $orderTable.updated_at as last_order_date")
                ->joinWith('client')
                ->joinWith('catalog')
                ->joinWith('lastOrder');
        if ($manager_id) {
            $query->leftJoin("$maTable", "$maTable.organization_id = $rspTable.rest_org_id");
            $query->where(["$rspTable.supp_org_id" => $vendor_id, "$rspTable.deleted" => false, "$maTable.manager_id" => $manager_id]);
        } else {
            $query->where(["$rspTable.supp_org_id" => $vendor_id, "$rspTable.deleted" => false]);
        }
        $query->groupBy("$rspTable.rest_org_id");
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $dataProvider->sort->attributes['client_name'] = [
            'asc' => ["$orgTable.name" => SORT_ASC],
            'desc' => ["$orgTable.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['catalog_name'] = [
            'asc' => ["$catTable.name" => SORT_ASC],
            'desc' => ["$catTable.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['last_order_date'] = [
            'asc' => ["$orderTable.updated_at" => SORT_ASC],
            'desc' => ["$orderTable.updated_at" => SORT_DESC],
        ];
        
        // grid filtering conditions
        $query->andFilterWhere([
            "$rspTable.invite" => $this->invite,
            "$catTable.id" => $this->cat_id,
        ]);
        
        $query->andFilterWhere(['or', 
            ['like', "$orgTable.name", $this->search_string],
            ['like', "$catTable.name", $this->search_string],
            ]);

        return $dataProvider;
    }
}
