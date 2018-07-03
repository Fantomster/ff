<?php

namespace api\common\models\merc\search;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use yii\helpers\ArrayHelper;
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
            [['amount','type'], 'number'],
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

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            $query->andWhere("recipient_guid = '$guid' and status = 'CONFIRMED'");
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'number' => $this->id,
            'status' => $this->status,
            'amount' => $this->amount,
            'date_doc' => $this->date_doc,

        ]);

        if($this->type == 2)
            $query->andWhere("recipient_guid = '$guid'");
        else
            $query->andWhere("sender_guid = '$guid'");

        if ( !empty($this->date_from) && !empty($this->date_to)) {
            $start_date = date('Y-m-d 00:00:00',strtotime($this->date_from));
            $end_date = date('Y-m-d 23:59:59',strtotime($this->date_to));
            $query->andFilterWhere(['between', 'date_doc', $start_date, $end_date]);
        }

        $query->andFilterWhere(['like', 'product_name', $this->product_name]);

        return $dataProvider;
    }

    public function getRecipientList()
    {
        $guid = mercDicconst::getSetting('enterprise_guid');
        if($this->type == 1)
            return array_merge(['' => 'Все'], ArrayHelper::map(MercVsd::find()->where("recipient_guid = '$guid'")->groupBy('sender_guid')->all(), 'sender_guid', 'sender_name'));
        else
            return array_merge(['' => 'Все'], ArrayHelper::map(MercVsd::find()->where("sender_guid = '$guid'")->groupBy('recipient_guid')->all(), 'recipient_guid', 'recipient_name'));
    }
}
