<?php

namespace common\models\search;

use common\models\Currency;
use common\models\OperatorCall;
use common\models\OperatorVendorComment;
use common\models\Order;
use common\models\Organization;
use common\models\Profile;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

class OperatorReportSearch extends Order
{
    /**
     * Description
     *
     * @var
     */
    public $user_id;
    public $date_from;
    public $date_to;

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params, $isTotal = false)
    {
        /**
         * Фильтр по дате заказа
         */
        if (!empty($params['date_from']) && !empty($params['date_to'])) {
            $dateFrom = date('Y-m-d H:i:s', strtotime(trim($params['date_from'])));
            $dateTo = date('Y-m-d H:i:s', strtotime(trim($params['date_to'])));
            $timeCondition = "a.created_at between '$dateFrom' and '$dateTo'";
        } else {
            $timeCondition = "a.created_at between now() - interval 7 day and now()";
        }

        $select = [
            'cnt_order'         => 'count(a.order_id)',
            'cnt_order_changed' => 'count(distinct d.order_id)',
            'operator_name'     => 'coalesce(c.username, c.email)',
            'dt'                => "DATE_FORMAT(a.created_at, '%Y-%m-%d')",
            'cnt_vendor'        => 'count(distinct b.vendor_id)',
            'status'            => new Expression("case b.status
                        when 1 then 'Ожидает подтверждения поставщика'
                        when 3 then 'Выполняется'
                        when 4 then 'Завершен'
                        when 5 then 'Отклонен поставщиком'
                        when 6 then 'Отменен'
                        else b.status
                    end"),
            'status_call_id'    => 'status_call_id',
            'avg_resolve_mins'  => 'round(avg(TIME_TO_SEC(TIMEDIFF(a.closed_at, a.created_at))) / 60, 2)',
        ];

        if ($isTotal) {
            $groupBy = [
                "c.email"
            ];
        } else {
            $groupBy = [
                "c.email",
                "dt",
                "status",
                "status_call_id"
            ];
        }

        $query = (new Query())->select($select)
            ->from(Order::tableName() . " b")
            ->leftJoin(OperatorCall::tableName() . ' as a', 'a.order_id = b.id')
            ->leftJoin(User::tableName() . ' as c', 'c.id = a.operator_id')
            ->leftJoin("order_chat as d", "d.order_id = b.id 
                                and b.vendor_id = d.recipient_id 
                                and d.is_system = 1
                                and d.message like '%изменил детали заказа%'")
            ->where("a.order_id = b.id
                                and a.operator_id = c.id
                                and $timeCondition
                                and b.status in (3, 4)")
            ->groupBy($groupBy)
            ->orderBy("a.created_at, email, status");

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $dataProvider;
    }

}