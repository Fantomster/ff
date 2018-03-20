<?php

namespace common\models\search;

use common\models\CatalogBaseGoods;
use Yii;
use common\models\guides\Guide;
use yii\data\ActiveDataProvider;

/**
 * Description of GuideSearch
 *
 * @author elbabuino
 */
class GuideSearch extends Guide
{

    public $date_from;
    public $color;
    public $date_to;
    public $vendor_id;
    public $searchString;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['searchString', 'client_id', 'name', 'updated_at'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $client_id)
    {

        $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
        if ($from) {
            $t1_f = $from->format('Y-m-d H:i:s');
        }

        $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
        if ($to) {
            $to->add(new \DateInterval('P1D'));
            $t2_f = $to->format('Y-m-d H:i:s');
        }

        $query = Guide::find()->distinct()->joinWith('guideProducts.baseProduct.vendor');
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->where([
            'client_id' => $client_id,
            'type' => Guide::TYPE_GUIDE,
        ]);

        if (isset($t1_f)) {
            $query->andFilterWhere(['>=', Guide::tableName() . '.created_at', $t1_f]);
        }
        if (isset($t2_f)) {
            $query->andFilterWhere(['<=', Guide::tableName() . '.created_at', $t2_f]);
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', 'name', $this->searchString]);
        $query->andFilterWhere(['like', 'color', $this->color]);

        if (isset($this->vendor_id)) {
            $query->andWhere(['=', CatalogBaseGoods::tableName() . '.supp_org_id', $this->vendor_id]);
        }

        return $dataProvider;
    }
}
