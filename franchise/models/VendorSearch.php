<?php

namespace franchise\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;

/**
 * Description of VendorSearch
 *
 * @author sharaf
 */
class VendorSearch extends Organization {
    
    public $clientCount;
    public $orderCount;
    public $orderSum;
    public $date_from;
    public $date_to;
    
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'type_id'], 'integer'],
            [['name', 'clientCount', 'orderCount', 'orderSum', 'created_at', 'contact_name', 'phone', 'date_from', 'date_to'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
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
    public function search($params, $franchisee_id) {
        $query = Organization::find();

        $query->joinWith("franchiseeAssotiate");
        $query->joinWith("assotiates");
        $query->where(['type_id' => Organization::TYPE_SUPPLIER, 'franchisee_id' => $franchisee_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_ASC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $dataProvider->sort->attributes['clientCount'] = [
            'asc' => ["clientCount" => SORT_ASC],
            'desc' => ["clientCount" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['orderCount'] = [
            'asc' => ["orderCount" => SORT_ASC],
            'desc' => ["orderCount" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['orderSum'] = [
            'asc' => ["orderSum" => SORT_ASC],
            'desc' => ["orderSum" => SORT_DESC],
        ];

        // grid filtering conditions
        $query->andFilterWhere([
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'phone', $this->phone])
                ->andFilterWhere(['like', 'contact_name', $this->website]);
        
        return $dataProvider;
    }
}
