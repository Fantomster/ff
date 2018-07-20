<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Order;
use common\models\OrderAttachment;
use common\models\OrderAssignment;

/**
 * Description of OrderWithAttachmentsSearch
 *
 * @author elbabuino
 */
class OrderWithAttachmentsSearch extends OrderAttachment {

    public $order_id;
    public $assigned_to;
    public $is_processed;
    public $created_at_range;

    /**
     * @inheritdoc
     */
    public function rules(): array {
        return [
            [['order_id', 'id', 'file', 'created_at', 'assigned_to', 'is_processed', 'created_at_range'], 'safe'],
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
    public function search($params) {

        $attachmentTable = OrderAttachment::tableName();
        $orderTable = Order::tableName();
        $assignmentTable = OrderAssignment::tableName();

        $editableOrders = [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            Order::STATUS_PROCESSING,
            Order::STATUS_DONE,
        ];
        
        $query = OrderAttachment::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['order_id' => SORT_DESC]],
            'pagination' => ['pageSize' => 20]
        ]);

        $query->joinWith('order', true);

        $query->joinWith('assignment', true);

        $query->where(["order.status" => $editableOrders]);

        $query->select([
            "$attachmentTable.order_id",
            "$attachmentTable.id as id",
            "$attachmentTable.file as file",
            "$attachmentTable.created_at as created_at",
            "$assignmentTable.assigned_to as assigned_to",
            "$assignmentTable.is_processed as is_processed",
        ]);

        $dataProvider->sort->attributes['order_id'] = [
            'asc' => ["$attachmentTable.order_id" => SORT_ASC],
            'desc' => ["$attachmentTable.order_id" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['assigned_to'] = [
            'asc' => ["$assignmentTable.assigned_to as assigned_to" => SORT_ASC],
            'desc' => ["$assignmentTable.assigned_to as assigned_to" => SORT_DESC],
        ];
        $dataProvider->sort->attributes['is_processed'] = [
            'asc' => ["$assignmentTable.is_processed as is_processed" => SORT_ASC],
            'desc' => ["$assignmentTable.is_processed as is_processed" => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            "$orderTable.id" => $this->order_id,
            "$assignmentTable.assigned_to" => $this->assigned_to,
        ]);

        if (!empty($this->created_at_range) && strpos($this->created_at_range, '-') !== false) {
            list($start_date, $end_date) = explode(' - ', $this->created_at_range);
            $query->andFilterWhere(["between", "$attachmentTable.created_at", $start_date, $end_date]);
        }

        return $dataProvider;
    }

}
