<?php

namespace common\models\search;

use common\models\RelationSuppRest;
use common\models\ManagerAssociate;
use common\models\Organization;
use common\models\Order;
use common\models\Catalog;
use common\models\RelationSuppRestPotential;
use common\models\RelationUserOrganization;
use common\models\TestVendors;
use yii\data\ActiveDataProvider;
use yii\db\Query;

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
        $rspPTable = RelationSuppRestPotential::tableName();
        $orgTable = Organization::tableName();
        $catTable = Catalog::tableName();
        $rel = TestVendors::find()->indexBy('id')->all();
        $relArray = [];
        foreach ($rel as $one){
            $relArray[] = $one->vendor_id;
        }

        $query = RelationSuppRest::find()
                ->select("$rspTable.rest_org_id, $rspTable.supp_org_id, $rspTable.cat_id, $rspTable.invite, 
                $rspTable.created_at, $rspTable.updated_at, $rspTable.status, $rspTable.uploaded_catalog, $rspTable.uploaded_processed, 
                $rspTable.is_from_market, $rspTable.deleted,           
                $orgTable.name as vendor_name, $catTable.status as catalog_status")
                ->joinWith('catalog')
                ->joinWith('vendor');
        $query->where(["$rspTable.rest_org_id" => $client_id, "$rspTable.deleted" => false])->andWhere(['not in', "$orgTable.id", $relArray]);

        $query2 = RelationSuppRestPotential::find()
            ->select("$rspPTable.rest_org_id, $rspPTable.supp_org_id, $rspPTable.cat_id, $rspPTable.invite, 
                $rspPTable.created_at, $rspPTable.updated_at, $rspPTable.status, $rspPTable.uploaded_catalog, $rspPTable.uploaded_processed, 
                $rspPTable.is_from_market, $rspPTable.deleted, 
                $orgTable.name as vendor_name, $catTable.status as catalog_status")
            ->joinWith('catalog')
            ->joinWith('vendor');
        $query2->where(["$rspPTable.rest_org_id" => $client_id, "$rspPTable.deleted" => false]);

        $query3 = RelationSuppRest::find();
        $query3->select('*')->from(['u' => $query->union($query2, true)]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query3,
            'sort'=> ['defaultOrder' =>
                ['vendor_name'=>SORT_ASC],
                'attributes' => [
                    'vendor_name',
                    'status' =>[
                            'asc' => ['invite' => SORT_ASC, 'catalog_status' => SORT_ASC, 'status' => SORT_ASC,],
                            'desc' => ['invite' => SORT_DESC, 'catalog_status' => SORT_DESC, 'status' => SORT_DESC],
                    ],
                ],
            ],
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

        // grid filtering conditions
        $query3->andFilterWhere([
            "invite" => $this->invite,
            "catalog_id" => $this->cat_id,
        ]);

        $query3->andFilterWhere(['or',
            ['like', "vendor_name", $this->search_string],
        ]);

        /*$dataProvider->sort->attributes['vendor_name'] = [
            'asc' => ["vendor_name" => SORT_ASC],
            'desc' => ["vendor_name" => SORT_DESC],

        ];
        $dataProvider->sort->attributes['status'] = [
            'asc' => ["status" => SORT_ASC],
            'desc' => ["status" => SORT_DESC],

        ];*/

        return $dataProvider;
    }
}
