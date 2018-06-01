<?php

namespace api\common\models\merc\search;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class mercVSDSearch extends MercVsd
{
    public $date_from;
    public $date_to;

    public function rules()
    {
        return [
            [['date_doc', 'production_date', 'guid', 'date_from', 'date_to'], 'safe'],
            [['amount'], 'number'],
            [['uuid', 'number', 'status', 'product_name', 'unit', 'recipient_name','type', 'consignor'], 'string', 'max' => 255],
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
        $query = MercVsd::find()->where(['guid' => $guid]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            $query->andWhere("consignor = '$guid'");
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'number' => $this->id,
            'status' => $this->status,
            'amount' => $this->amount,
            'date_doc' => $this->date_doc,

        ]);

        $query->andFilterWhere(['like', 'product_name', $this->product_name])
            ->andFilterWhere(['like', 'recipient_name', $this->product_name]);

        return $dataProvider;
    }

    public function getRecipientList()
    {
        return [];
    }
}
