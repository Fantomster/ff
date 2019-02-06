<?php

namespace backend\models;

use common\models\Currency;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CatalogBaseGoodsSearch represents the model behind the search form about `common\models\CatalogBaseGoods`.
 *
 * @property string $num_code   [varchar(255)]  Цифровой код валюты
 * @property string $iso_code   [varchar(3)]  ISO-код валюты
 * @property int    $signs      [int(11)]  Количество знаков(разрядов) после запятой
 * @property bool   $is_active  [tinyint(1)]  Показатель состояния активности валюты в системе (0 - не активна, 1 -
 *           активна)
 * @property string $old_symbol [varchar(255)]  Прежнее односимвольное обозначение валюты
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
     * @return ActiveDataProvider
     */
    public function search($params = null)
    {
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
            'text'     => $this->text,
            'num_code' => $this->num_code,
            'iso_code' => $this->iso_code,
        ]);

        return $dataProvider;
    }
}
