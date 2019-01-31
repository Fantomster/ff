<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CatalogBaseGoods;

/**
 * CatalogBaseGoodsSearch represents the model behind the search form about `common\models\CatalogBaseGoods`.
 */
class CatalogBaseGoodsSearch extends CatalogBaseGoods
{
    public $vendor_name;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cat_id', 'status', 'market_place', 'deleted', 'supp_org_id', 'category_id'], 'integer'],
            [['article', 'product', 'created_at', 'updated_at', 'note', 'ed', 'image', 'brand', 'region', 'weight', 'vendor_name'], 'safe'],
            [['price', 'units'], 'number'],
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
     * @param null  $id
     * @return ActiveDataProvider
     */
    public function search($params = null, $id = null)
    {
        $goodsTable = CatalogBaseGoods::tableName();
        $vendorTable = \common\models\Organization::tableName();
        
        $query = CatalogBaseGoods::find();
        $query->joinWith(['vendor']);
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $dataProvider->sort->attributes['vendor_name'] = [
            'asc' => ["$vendorTable.name" => SORT_ASC],
            'desc' => ["$vendorTable.name" => SORT_DESC],
        ];
        
        // grid filtering conditions
        $query->andFilterWhere([
            $goodsTable.'.id' => $this->id,
            'cat_id' => $this->cat_id,
            'status' => $this->status,
            'market_place' => $this->market_place,
            'deleted' => $this->deleted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'supp_org_id' => $this->supp_org_id,
            'price' => $this->price,
            'units' => $this->units,
            'category_id' => $this->category_id,
        ]);

        $query->andFilterWhere(['like', 'article', $this->article])
            ->andFilterWhere(['like', 'product', $this->product])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'ed', $this->ed])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'brand', $this->brand])
            ->andFilterWhere(['like', 'region', $this->region])
            ->andFilterWhere(['like', 'weight', $this->weight])
            ->andFilterWhere(['like', "$vendorTable.name", $this->vendor_name]);
        
        $query->andFilterWhere(['deleted' => CatalogBaseGoods::DELETED_OFF]);

        return $dataProvider;
    }
}
