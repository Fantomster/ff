<?php

namespace common\models\search;

use common\models\CatalogBaseGoods;
use common\models\CatalogGoodsBlocked;
use common\models\Organization;
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
    public $updated_date_from;
    public $color;
    public $updated_date_to;
    public $date_to;
    public $vendor_id;
    public $vendors;
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
    public function search(array $params, int $client_id): ActiveDataProvider
    {
        $this->load($params);

        $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_from . " 00:00:00");
        if ($from) {
            $t1_f = $from->format('Y-m-d H:i:s');
        }

        $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->date_to . " 00:00:00");
        if ($to) {
            $to->add(new \DateInterval('P1D'));
            $t2_f = $to->format('Y-m-d H:i:s');
        }

        $updated_from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->updated_date_from . " 00:00:00");
        if ($updated_from) {
            $updated_t1_f = $updated_from->format('Y-m-d H:i:s');
        }

        $updated_to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->updated_date_to . " 00:00:00");
        if ($updated_to) {
            $updated_t2_f = $updated_to->format('Y-m-d H:i:s');
        }

        $query = Guide::find()->distinct()->joinWith('guideProducts.baseProduct.vendor');
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

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

        if (isset($updated_t1_f)) {
            $query->andFilterWhere(['>=', Guide::tableName() . '.updated_at', $updated_t1_f]);
        }
        if (isset($updated_t2_f)) {
            $query->andFilterWhere(['<=', Guide::tableName() . '.updated_at', $updated_t2_f]);
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', 'guide.name', $this->searchString]);
        $query->andFilterWhere(['like', 'color', $this->color]);

        if (isset($this->vendor_id)) {
            $query->andWhere(['=', CatalogBaseGoods::tableName() . '.supp_org_id', $this->vendor_id]);
        }

        if (isset($this->vendors)) {
            $query->andWhere(['IN', CatalogBaseGoods::tableName() . '.supp_org_id', $this->vendors]);
        }

        //Добавляем блокировку запрещенных товаров
        $blockedItems = implode(",", CatalogGoodsBlocked::getBlockedList($client_id));
        if (!empty($blockedItems)) {
            $query->andWhere(["AND",
                "cbg_id NOT IN ($blockedItems)"
            ]);
        }

        return $dataProvider;
    }
}
