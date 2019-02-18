<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PaymentSearch represents the model behind the search form about `common\models\Payment`.
 */
class PaymentSearch extends Payment
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_id', 'organization_id', 'type_payment'], 'integer'],
            [['total'], 'number'],
            [['receipt_number', 'email', 'phone', 'created_at', 'updated_at', 'date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Payment::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 10
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['payment_id' => $this->payment_id])
            ->andFilterWhere(['like', 'receipt_number', $this->receipt_number])
            ->andFilterWhere(['total' => $this->total]);

        if (isset($this->date) && !empty($this->date)) {
            $query->andFilterWhere(['date' => date('Y-m-d', strtotime($this->date))]);
        }

        if (isset($this->organization_id) && !empty($this->organization_id) && $this->organization_id != 0) {
            $query->andFilterWhere(['organization_id' => $this->organization_id]);
        }

        if (isset($this->type_payment) && !empty($this->type_payment) && $this->type_payment != 0) {
            $query->andFilterWhere(['type_payment' => $this->type_payment]);
        }

        return $dataProvider;
    }
}
