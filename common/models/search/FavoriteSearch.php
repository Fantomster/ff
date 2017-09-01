<?php
namespace common\models\search;

use common\models\CatalogBaseGoods;
use common\models\Order;
use common\models\OrderContent;
use yii\data\ActiveDataProvider;

/**
 * Description of FavoriteSearch
 *
 * @author elbabuino
 */
class FavoriteSearch extends CatalogBaseGoods {
    public $searchString;
    
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['searchString', 'id', 'product', 'order.created_at'], 'safe'],
        ];
    }
    
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param integer $guideId
     *
     * @return ActiveDataProvider
     */
    public function search($params, $clientId) {
        
        $cbgTable = CatalogBaseGoods::tableName();
        $orderTable = Order::tableName();
        $ordContentTable = OrderContent::tableName();
        
        $query = CatalogBaseGoods::find();
        $query->leftJoin($ordContentTable, "$cbgTable.id=$ordContentTable.product_id");
        $query->leftJoin($orderTable, "$ordContentTable.order_id=$orderTable.id");

        // add conditions that should always apply here
        //where ord.client_id = 1 and cbg.status=1 and cbg.deleted = 0
        $query->where(["$orderTable.client_id" => $clientId, "$cbgTable.status" => CatalogBaseGoods::STATUS_ON, "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF]);
        $query->groupBy(["$cbgTable.id"]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
          //  'sort' => ['defaultOrder' => ["$orderTable.created_at" => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', "$cbgTable.product", $this->searchString]);

        return $dataProvider;
    }
}
