<?php

namespace common\models\search;

use api\common\models\merc\MercStockEntry;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class MercStockEntrySearch
 *
 * @package common\models\search
 */
class MercStockEntrySearch extends MercStockEntry
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['date_doc', 'production_date', 'date_from_production_date', 'date_to', 'date_to_production_date', 'date_from_expiry_date', 'date_to_expiry_date'], 'safe'],
            [['amount', 'is_expiry'], 'number'],
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
     * @param array  $params
     * @param string $guid
     * @return ActiveDataProvider
     */
    public function search($params, $guid)
    {
        $query = MercStockEntry::find()->where(['owner_guid' => $guid]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->andWhere('amount > 0')->andWhere(['active' => true, 'last' => true]);

        if (isset($params['production_name']) && !empty($params['production_name'])) {
            $query->andWhere(['product_name' => $params['production_name']]);
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $query->andWhere(['like', 'product_name', $params['name']]);
        }
        if (isset($params['producer_guid']) && !empty($params['producer_guid'])) {
            $query->andWhere(['producer_guid' => $params['producer_guid']]);
        }
        if (isset($params['producer_guid']) && !empty($params['producer_guid'])) {
            $query->andWhere(['producer_guid' => $params['producer_guid']]);
        }
        foreach (['create_date', 'expiry_date', 'production_date'] as $label) {
            if (isset($params[$label]) && !empty($params[$label])) {
                if (isset($params[$label]['from']) && !empty($params[$label]['from'])) {
                    $start_date = date('Y-m-d 00:00:00', strtotime($params[$label]['from']));
                } else {
                    $start_date = date('Y-m-d 00:00:00', strtotime('01.01.2000'));
                }
                if (isset($params[$label]['to']) && !empty($params[$label]['to'])) {
                    $end_date = date('Y-m-d 23:59:59', strtotime($params[$label]['to']));
                } else {
                    $end_date = date('Y-m-d 23:59:59');
                }
                $query->andFilterWhere(['between', 'STR_TO_DATE(' . $label . ', \'%Y-%c-%e %H:%i:%s\')', $start_date, $end_date]);
            }
        }

        return $dataProvider;
    }
}
