<?php

namespace api\common\models\rkws;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\common\models\RkWaybilldata;

/**
 * RkWaybilldataSearch represents the model behind the search form of `\api\common\models\RkWaybilldata`.
 */
class RkWaybilldataSearch extends RkWaybilldata
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'waybill_id', 'product_rid', 'vat', 'munit_rid', 'product_id', 'org', 'vat_included'], 'integer'],
            [['quant', 'sum', 'koef', 'defsum', 'defquant'], 'number'],
            [['created_at', 'updated_at', 'linked_at'], 'safe'],
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

        $query = RkWaybilldata::find()->select('rk_waybill_data.*, rk_product.denom as pdenom ')->andWhere(['waybill_id' => Yii::$app->request->get('waybill_id')])
            ->leftJoin('rk_product', 'rk_product.id = product_rid')
            ->leftJoin($dbname.'.catalog_base_goods', 'catalog_base_goods.id = product_id');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
                'attributes' => [
                    'product_id',
                    'fproductnameProduct' => [
                        'desc' => ['catalog_base_goods.product' => SORT_DESC],
                        'asc' => ['catalog_base_goods.product' => SORT_ASC],
                    ]
                ],
                'defaultOrder' => [
                    'fproductnameProduct' => SORT_ASC
                ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        /*$query->andFilterWhere([
        ]);*/

        return $dataProvider;
    }
}
