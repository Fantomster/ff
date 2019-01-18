<?php

namespace api\common\models\merc\search;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercStockEntry;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class mercStockEntrySearch extends MercStockEntry
{
    public $date_from_production_date;
    public $date_to_production_date;
    public $date_from_expiry_date;
    public $date_to_expiry_date;
    public $is_expiry;

    public function rules()
    {
        return [
            [['date_doc', 'production_date', 'date_from_production_date', 'date_to', 'date_to_production_date', 'date_from_expiry_date', 'date_to_expiry_date'], 'safe'],
            [['amount','is_expiry'], 'number'],
            [['uuid', 'product_name', 'unit', 'producer_name'], 'string', 'max' => 255],
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
            'pagination' => [
                'page' => isset($params['page']) ? ($params['page'] - 1) : 0,
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : null,
            ],
        ]);

        $query->andWhere('amount > 0')->andWhere(['active'=>true, 'last' => true]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if ( !empty($this->date_from_production_date) && !empty($this->date_to_production_date)) {
        $start_date = date('Y-m-d 00:00:00',strtotime($this->date_from_production_date));
        $end_date = date('Y-m-d 23:59:59',strtotime($this->date_to_production_date));
            $query->andFilterWhere(['between', 'STR_TO_DATE(production_date, \'%Y-%c-%e %H:%i:%s\')', $start_date, $end_date]);
        }

        if ( !empty($this->date_from_expiry_date) && !empty($this->date_to_expiry_date)) {
            $start_date = date('Y-m-d 00:00:00',strtotime($this->date_from_expiry_date));
            $end_date = date('Y-m-d 23:59:59',strtotime($this->date_to_expiry_date));
            $query->andFilterWhere(['between', 'STR_TO_DATE(expiry_date, \'%Y-%c-%e %H:%i:%s\')', $start_date, $end_date]);
        }

        if($this->is_expiry == 1)
        {
            $query->andWhere('expiry_date < NOW()');
        }

        $query->andFilterWhere(['like', 'product_name', $this->product_name]);
        $query->andFilterWhere(['like', 'producer_name', $this->producer_name]);

        return $dataProvider;
    }
}
