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
class VendorSearch extends RelationSuppRest {
    
    public $vendor_name;
    public $search_string;
    public $catalog_status;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'supp_org_id', 'invite', 'catalog_status'], 'integer'],
            [['vendor_name', 'search_string'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), ['vendor_name', 'search_string', 'catalog_status']);
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
    public function search($params, $client_id, $tmp = false)
    {
        $rspTable = RelationSuppRest::tableName();
        $orgTable = Organization::tableName();
        $catTable = Catalog::tableName();

        $query = RelationSuppRest::find()
                ->select("$rspTable.*, $orgTable.name as vendor_name, $catTable.status as catalog_status")
                ->joinWith('catalog')
                ->joinWith('vendor');
        $query->where(["$rspTable.rest_org_id" => $client_id, "$rspTable.deleted" => false]);
//        $query->groupBy("$rspTable.supp_org_id");
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['supp_org_id'=>SORT_ASC]],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $dataProvider->sort->attributes['vendor_name'] = [
            'asc' => ["$orgTable.name" => SORT_ASC],
            'desc' => ["$orgTable.name" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['catalog_status'] = [
            'asc' => ["$catTable.catalog_status" => SORT_ASC],
            'desc' => ["$catTable.catalog_status" => SORT_DESC],
        ];
        
        // grid filtering conditions
        $query->andFilterWhere([
            "$rspTable.invite" => $this->invite,
            "$catTable.id" => $this->cat_id,
        ]);
        
        $query->andFilterWhere(['or', 
            ['like', "$orgTable.name", $this->search_string],
            ]);

        return $dataProvider;
    }
}
