<?php

namespace api\common\models\merc\search;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercStockEntry;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class mercStockEntrySearch extends MercStockEntry
{
    public $date_from;
    public $date_to;

    public function rules()
    {
        return [
            [['date_doc', 'production_date', 'date_from', 'date_to'], 'safe'],
            [['amount'], 'number'],
            [['uuid', 'number', 'status', 'product_name', 'unit'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
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
        $guid = mercDicconst::getSetting('enterprise_guid');
        $query = MercStockEntry::find()->where(['owner_guid' => $guid]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'number' => $this->id,
            'status' => $this->status,
            'amount' => $this->amount,
        ]);

        if ( !empty($this->date_from) && !empty($this->date_to)) {
            $start_date = date('Y-m-d 00:00:00',strtotime($this->date_from));
            $end_date = date('Y-m-d 23:59:59',strtotime($this->date_to));
            $query->andFilterWhere(['between', 'date_doc', $start_date, $end_date]);
        }

        $query->andFilterWhere(['like', 'product_name', $this->product_name]);

        return $dataProvider;
    }
}
