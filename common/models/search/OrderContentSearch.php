<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\OrderContent;
use common\models\CatalogBaseGoods;

/**
 * OrderContentSearch represents the model behind the search form about `common\models\OrderContent`.
 */
class OrderContentSearch extends OrderContent
{
    public $total;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'product_id', 'quantity', 'price', 'initial_quantity', 'total'], 'integer'],
            [['product.product'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), ['product.product']);
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = OrderContent::find();
        $productTable = CatalogBaseGoods::tableName();
        $contentTable = OrderContent::tableName();

        $query->select([$contentTable.'.*, product.product, (' . $contentTable.'.quantity * ' . $contentTable . '.price) AS total']);
        
        // add conditions that should always apply here
        $query->joinWith(['product' => function ($query) use ($productTable) {
            $query->from(['product' => $productTable]);
        }]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $addSortAttributes = ['product.product', 'quantity', 'price', 'total'];
        foreach ($addSortAttributes as $addSortAttribute) {
            $dataProvider->sort->attributes[$addSortAttribute] = [
                'asc' => [$addSortAttribute => SORT_ASC],
                'desc' => [$addSortAttribute => SORT_DESC],
            ];
        }
        
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'initial_quantity' => $this->initial_quantity,
        ]);

        return $dataProvider;
    }
}