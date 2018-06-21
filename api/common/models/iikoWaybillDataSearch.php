<?php

namespace api\common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\common\models\iiko\iikoWaybillData;
use yii\data\ArrayDataProvider;

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
        $query = iikoWaybillData::find()
            ->select('iiko_waybill_data.*, iiko_product.denom as pdenom')
            ->leftJoin('iiko_product', 'iiko_product.id = product_rid')
            ->where(['waybill_id' => Yii::$app->request->get('waybill_id')])->all();

        foreach ($query as $key=>$value)
        {
            $data = $value->attributes;
            $data['fproductnameProduct'] = $value->fproductnameProduct;
            $arr[$key] = $data;
        }

        // add conditions that should always apply here

        $dataProvider = new ArrayDataProvider([
            'key' => 'id',
            'allModels' => $query,

        ]);

       $dataProvider->setSort([
            'attributes' => [
                'product_id'=>[
                    'asc' => ['catalog_base_goods.id' => SORT_ASC],
                    'desc' => ['catalog_base_goods.id' => SORT_DESC],
                ],
                'fproductnameProduct'
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

        return $dataProvider;
    }
}
