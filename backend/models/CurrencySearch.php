<?php

namespace backend\models;

use common\models\Currency;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CatalogBaseGoods;

/**
 * CatalogBaseGoodsSearch represents the model behind the search form about `common\models\CatalogBaseGoods`.
 */
class CurrencySearch extends Currency
{
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'is_active'], 'integer'],
            [['text', 'num_code', 'iso_code'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params = null, $id = null)
    {
        $currencyTable = Currency::tableName();
        
        $query = Currency::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        // grid filtering conditions
        $query->andFilterWhere([
            'text' => $this->text,
            'num_code' => $this->num_code,
            'iso_code' => $this->iso_code,
        ]);
        
        return $dataProvider;
    }
}
