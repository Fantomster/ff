<?php

namespace common\models\search;

use common\models\RelationSuppRest;
use common\models\Profile;
use common\models\Organization;
use common\models\Order;
use common\models\Catalog;
use yii\data\ActiveDataProvider;

/**
 * Description of RelationSuppRest
 *
 * @author sharaf
 */
class RelationSuppRestSearch extends RelationSuppRest {
    
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
            [['id', 'status', 'vendor_manager_id', 'cat_id', 'rest_org_id', 'invite'], 'integer'],
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
    public function search($params, $vendor_id)
    {
        $rspTable = RelationSuppRest::tableName();
        $orgTable = Organization::tableName();
        $profTable = Profile::tableName();
        $orderTable = Order::tableName();
        $catTable = Catalog::tableName();

        $query = RelationSuppRest::find()
                ->select("$rspTable.*, $orgTable.name as client_name, $catTable.name as catalog_name, `$orderTable`.updated_at as last_order_date, $profTable.full_name as manager_name")
                ->joinWith('client')
                ->joinWith('catalog')
                ->joinWith('managerProfile')
                ->joinWith('lastOrder')
                ->where(["$rspTable.supp_org_id" => $vendor_id])
                ->groupBy("$rspTable.rest_org_id");
        
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
        $dataProvider->sort->attributes['manager_name'] = [
            'asc' => ["$profTable.full_name" => SORT_ASC],
            'desc' => ["$profTable.full_name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['last_order_date'] = [
            'asc' => ["$orderTable.updated_at" => SORT_ASC],
            'desc' => ["$orderTable.updated_at" => SORT_DESC],
        ];
        
        // grid filtering conditions
        $query->andFilterWhere([
            "$rspTable.invite" => $this->invite,
            "$catTable.id" => $this->cat_id,
            "$profTable.vendor_manager_id" => $this->vendor_manager_id,
        ]);
        
        $query->andFilterWhere(['or', 
            ['like', "$orgTable.name", $this->search_string],
            ['like', "$catTable.name", $this->search_string],
            ['like', "$profTable.full_name", $this->search_string],
            ]);

        return $dataProvider;
    }
}
