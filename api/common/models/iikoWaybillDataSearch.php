<?php

namespace api\common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\common\models\iiko\iikoWaybillData;

/**
 * iikoWaybillDataSearch represents the model behind the search form of `api\common\models\iiko\iikoWaybillData`.
 */
class iikoWaybillDataSearch extends iikoWaybillData
{

    public $fproductnameProduct;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'waybill_id', 'product_id', 'product_rid', 'org', 'vat', 'vat_included'], 'integer'],
            [['munit', 'created_at', 'updated_at', 'linked_at', 'fproductnameProduct'], 'safe'],
            [['sum', 'quant', 'defsum', 'defquant', 'koef'], 'number'],
        ];
    }


    /**
     * {@inheritdoc}
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
        $dbname = explode("=", Yii::$app->db->dsn);
        $dbname = $dbname[2];
        $query = iikoWaybillData::find()
            ->select('iiko_waybill_data.*, iiko_product.denom as pdenom')
            ->leftJoin('iiko_product', 'iiko_product.id = product_rid')
            ->leftJoin($dbname.'.catalog_base_goods', 'catalog_base_goods.id = product_id')
            ->where(['waybill_id' => Yii::$app->request->get('waybill_id')]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'fproductnameProduct' => SORT_ASC,
                ]
            ],
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'product_id'=>[
                    'asc' => ['catalog_base_goods.id' => SORT_ASC],
                    'desc' => ['catalog_base_goods.id' => SORT_DESC],
                ],
                'fproductnameProduct' => [
                    'desc' => ['catalog_base_goods.product' => SORT_DESC],
                    'asc' => ['catalog_base_goods.product' => SORT_ASC],
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }



        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'waybill_id' => $this->waybill_id,
            'product_id' => $this->product_id,
            'product_rid' => $this->product_rid,
            'org' => $this->org,
            'vat' => $this->vat,
            'vat_included' => $this->vat_included,
            'sum' => $this->sum,
            'quant' => $this->quant,
            'defsum' => $this->defsum,
            'defquant' => $this->defquant,
            'koef' => $this->koef,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'linked_at' => $this->linked_at,
        ]);

        $query->andFilterWhere(['like', 'munit', $this->munit]);

        return $dataProvider;
    }
}
